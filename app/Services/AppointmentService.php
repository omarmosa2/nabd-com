<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\ClinicStatus;
use App\Enums\VisitType;
use App\Events\AppointmentCancelled;
use App\Events\AppointmentCreated;
use App\Events\AppointmentDeleted;
use App\Events\AppointmentStatusChanged;
use App\Events\AppointmentUpdated;
use App\Exceptions\AppointmentConflictException;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    public const DEFAULT_DURATION_MINUTES = 30;
    public const CONFLICT_WINDOW_MINUTES = 30;

    public function paginate(array $filters, int $perPage = 25, ?User $viewer = null)
    {
        $query = Appointment::with(['patient', 'doctor', 'clinic']);

        if ($viewer && $viewer->isDoctor()) {
            $query->where('doctor_id', $viewer->id);
        }

        if (!empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }
        if (!empty($filters['clinic_id'])) {
            $query->where('clinic_id', $filters['clinic_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['date'])) {
            $query->whereDate('appointment_date', $filters['date']);
        }
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->betweenDates($filters['date_from'], $filters['date_to']);
        } elseif (!empty($filters['date_from'])) {
            $query->where('appointment_date', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        } elseif (!empty($filters['date_to'])) {
            $query->where('appointment_date', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->whereHas('patient', fn (Builder $q) => $q
                ->where('full_name', 'like', "%{$term}%")
                ->orWhere('file_number', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%"));
        }

        return $query->orderBy('appointment_date')->paginate($perPage);
    }

    public function calendar(int $year, int $month, array $filters = [], ?User $viewer = null): array
    {
        $query = Appointment::with(['patient', 'doctor', 'clinic'])
            ->inMonth($year, $month)
            ->whereIn('status', [
                AppointmentStatus::Scheduled->value,
                AppointmentStatus::Completed->value,
                AppointmentStatus::ConvertedToVisit->value,
            ]);

        if ($viewer && $viewer->isDoctor()) {
            $query->where('doctor_id', $viewer->id);
        }
        if (!empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }
        if (!empty($filters['clinic_id'])) {
            $query->where('clinic_id', $filters['clinic_id']);
        }

        $appointments = $query->orderBy('appointment_date')->get();

        return $appointments
            ->groupBy(fn (Appointment $a) => $a->appointment_date->toDateString())
            ->map(fn (Collection $items, string $date) => [
                'date' => $date,
                'count' => $items->count(),
                'appointments' => $items->map->toArray()->values(),
            ])
            ->values()
            ->toArray();
    }

    public function createAppointment(array $data, ?User $actor = null): Appointment
    {
        $duration = (int) ($data['duration_minutes'] ?? self::DEFAULT_DURATION_MINUTES);
        $date = Carbon::parse($data['appointment_date']);

        $this->assertClinicAcceptsAppointments((int) $data['clinic_id']);
        $this->assertDoctorAcceptsAppointments((int) $data['doctor_id']);
        $this->assertNoConflict(
            doctorId: (int) $data['doctor_id'],
            date: $date,
            duration: $duration,
        );

        return DB::transaction(function () use ($data, $duration, $date, $actor) {
            $appointment = Appointment::create([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'clinic_id' => $data['clinic_id'],
                'appointment_date' => $date,
                'duration_minutes' => $duration,
                'status' => AppointmentStatus::Scheduled->value,
                'notes' => $data['notes'] ?? null,
            ]);
            $appointment->load(['patient', 'doctor', 'clinic']);

            event(new AppointmentCreated($appointment, $actor));
            return $appointment;
        });
    }

    public function updateAppointment(Appointment $appointment, array $data, ?User $actor = null): Appointment
    {
        $newDate = isset($data['appointment_date'])
            ? Carbon::parse($data['appointment_date'])
            : $appointment->appointment_date;
        $newDuration = (int) ($data['duration_minutes'] ?? $appointment->duration_minutes);
        $newDoctor = isset($data['doctor_id']) ? (int) $data['doctor_id'] : $appointment->doctor_id;
        $newClinic = isset($data['clinic_id']) ? (int) $data['clinic_id'] : $appointment->clinic_id;

        $significantChange =
            !$newDate->equalTo($appointment->appointment_date) ||
            $newDuration !== $appointment->duration_minutes ||
            $newDoctor !== $appointment->doctor_id ||
            $newClinic !== $appointment->clinic_id;

        if ($newClinic !== $appointment->clinic_id) {
            $this->assertClinicAcceptsAppointments($newClinic);
        }
        if ($newDoctor !== $appointment->doctor_id) {
            $this->assertDoctorAcceptsAppointments($newDoctor);
        }

        if ($significantChange) {
            $this->assertNoConflict(
                doctorId: $newDoctor,
                date: $newDate,
                duration: $newDuration,
                ignoreId: $appointment->id,
            );
        }

        return DB::transaction(function () use ($appointment, $data, $newDate, $newDuration, $newClinic, $actor) {
            $previousStatus = $appointment->status;
            $appointment->fill([
                'patient_id' => $data['patient_id'] ?? $appointment->patient_id,
                'doctor_id' => $newDoctor,
                'clinic_id' => $newClinic,
                'appointment_date' => $newDate,
                'duration_minutes' => $newDuration,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $appointment->notes,
            ]);
            $appointment->save();
            $appointment->load(['patient', 'doctor', 'clinic']);

            event(new AppointmentUpdated($appointment, $actor));

            if (isset($data['status']) && $data['status'] !== $previousStatus->value) {
                $this->changeStatus($appointment, AppointmentStatus::from($data['status']), $actor, $data['cancel_reason'] ?? null);
                $appointment->refresh()->load(['patient', 'doctor', 'clinic']);
            }

            return $appointment;
        });
    }

    public function changeStatus(
        Appointment $appointment,
        AppointmentStatus $newStatus,
        ?User $actor = null,
        ?string $cancelReason = null,
    ): Appointment {
        if ($appointment->status === $newStatus) {
            return $appointment;
        }

        $previous = $appointment->status;
        $appointment->status = $newStatus;

        switch ($newStatus) {
            case AppointmentStatus::Cancelled:
                if ($previous !== AppointmentStatus::Scheduled) {
                    throw new \DomainException('لا يمكن إلغاء موعد في الحالة الحالية.');
                }
                $appointment->cancelled_at = now();
                $appointment->cancel_reason = $cancelReason;
                break;
            case AppointmentStatus::Completed:
                $appointment->completed_at = now();
                break;
            case AppointmentStatus::Missed:
                $appointment->completed_at = now();
                break;
            default:
                break;
        }

        $appointment->save();
        $appointment->load(['patient', 'doctor', 'clinic']);

        event(new AppointmentStatusChanged($appointment, $previous, $actor));
        if ($newStatus === AppointmentStatus::Cancelled) {
            event(new AppointmentCancelled($appointment, $cancelReason, $actor));
        }

        return $appointment;
    }

    public function cancelAppointment(Appointment $appointment, ?string $reason = null, ?User $actor = null): Appointment
    {
        return $this->changeStatus($appointment, AppointmentStatus::Cancelled, $actor, $reason);
    }

    public function markCompleted(Appointment $appointment, ?User $actor = null): Appointment
    {
        return $this->changeStatus($appointment, AppointmentStatus::Completed, $actor);
    }

    public function markMissed(Appointment $appointment, ?User $actor = null): Appointment
    {
        return $this->changeStatus($appointment, AppointmentStatus::Missed, $actor);
    }

    public function markMissedAppointments(): int
    {
        $cutoff = now();
        $count = 0;

        Appointment::query()
            ->where('status', AppointmentStatus::Scheduled->value)
            ->where('appointment_date', '<', $cutoff)
            ->chunkById(100, function (Collection $chunk) use (&$count) {
                foreach ($chunk as $apt) {
                    $endTime = $apt->appointment_date->copy()->addMinutes($apt->duration_minutes ?? self::DEFAULT_DURATION_MINUTES);
                    if ($endTime->isPast()) {
                        $this->markMissed($apt);
                        $count++;
                    }
                }
            });

        return $count;
    }

    public function deleteAppointment(Appointment $appointment, ?User $actor = null): void
    {
        if ($appointment->status === AppointmentStatus::ConvertedToVisit) {
            throw new \DomainException('لا يمكن حذف موعد تم تحويله إلى زيارة.');
        }
        $snapshot = $appointment->only([
            'id', 'patient_id', 'doctor_id', 'clinic_id', 'appointment_date', 'status',
        ]);
        $appointment->delete();
        event(new AppointmentDeleted($snapshot, $actor));
    }

    /**
     * @return array{available: bool, conflicts: array<int, array<string, mixed>>}
     */
    public function checkAvailability(int $doctorId, Carbon $date, int $duration = self::DEFAULT_DURATION_MINUTES, ?int $ignoreId = null): array
    {
        $conflicts = $this->findConflicts($doctorId, $date, $duration, $ignoreId);
        return [
            'available' => $conflicts->isEmpty(),
            'conflicts' => $conflicts->map(fn (Appointment $a) => [
                'id' => $a->id,
                'appointment_date' => $a->appointment_date->toDateTimeString(),
                'duration_minutes' => $a->duration_minutes,
                'patient_name' => $a->patient?->full_name,
                'status' => $a->status->value,
            ])->values()->toArray(),
        ];
    }

    protected function assertClinicAcceptsAppointments(int $clinicId): void
    {
        $clinic = Clinic::find($clinicId);
        if (!$clinic) {
            throw new AppointmentConflictException('العيادة غير موجودة.', []);
        }
        if (!$clinic->acceptsAppointments()) {
            throw new AppointmentConflictException(
                'العيادة غير نشطة ولا تستقبل مواعيد جديدة.',
                [['clinic_id' => $clinicId, 'status' => $clinic->status?->value]],
            );
        }
    }

    protected function assertDoctorAcceptsAppointments(int $doctorId): void
    {
        $doctor = User::find($doctorId);
        if (!$doctor) {
            throw new AppointmentConflictException('الطبيب غير موجود.', []);
        }
        if (!$doctor->isDoctor()) {
            throw new AppointmentConflictException('المستخدم المحدد ليس طبيباً.', []);
        }
        if ($doctor->isArchived()) {
            throw new AppointmentConflictException(
                'الطبيب مؤرشف ولا يستقبل مواعيد جديدة.',
                [['doctor_id' => $doctorId, 'archived_at' => $doctor->archived_at?->toDateTimeString()]],
            );
        }
        if (!$doctor->is_active) {
            throw new AppointmentConflictException(
                'الطبيب غير مفعّل ولا يستقبل مواعيد جديدة.',
                [['doctor_id' => $doctorId, 'is_active' => false]],
            );
        }
    }

    public function convertToVisit(Appointment $appointment, array $visitOverrides = [], ?User $actor = null): Visit
    {
        if (!$appointment->canBeConverted()) {
            throw new \DomainException('لا يمكن تحويل هذا الموعد إلى زيارة في الحالة الحالية.');
        }

        if ($appointment->visit_id) {
            return $appointment->visit()->with('procedures')->first();
        }

        return DB::transaction(function () use ($appointment, $visitOverrides, $actor) {
            /** @var VisitService $visitService */
            $visitService = app(VisitService::class);

            $doctor = $appointment->doctor;
            $patient = $appointment->patient;

            $isFreeReview = false;
            $visitType = VisitType::Examination;
            if (($visitOverrides['visit_type'] ?? null) === 'review') {
                $visitType = VisitType::Review;
                $isFreeReview = $visitService->isFreeReview($patient, $doctor);
            } elseif (!empty($visitOverrides['visit_type'])) {
                $visitType = VisitType::from($visitOverrides['visit_type']);
            }

            $examinationFee = $visitOverrides['examination_fee']
                ?? ($visitType === VisitType::Examination ? ($doctor->examination_fee ?? 0) : 0);

            $visit = Visit::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'clinic_id' => $appointment->clinic_id,
                'visit_date' => ($visitOverrides['visit_date'] ?? now())->toDateString(),
                'visit_type' => $visitType->value,
                'is_free_review' => $isFreeReview,
                'examination_fee' => $examinationFee,
                'amount_received' => $visitOverrides['amount_received'] ?? $examinationFee,
                'complex_discount' => $visitOverrides['complex_discount'] ?? 0,
                'doctor_discount' => $visitOverrides['doctor_discount'] ?? 0,
                'notes' => $visitOverrides['notes'] ?? $appointment->notes,
            ]);

            $appointment->update([
                'status' => AppointmentStatus::ConvertedToVisit->value,
                'visit_id' => $visit->id,
                'completed_at' => now(),
            ]);
            $appointment->load(['patient', 'doctor', 'clinic', 'visit']);

            event(new AppointmentStatusChanged($appointment, AppointmentStatus::Scheduled, $actor));
            event(new \App\Events\VisitCreated($visit));

            return $visit;
        });
    }

    protected function assertNoConflict(int $doctorId, Carbon $date, int $duration, ?int $ignoreId = null): void
    {
        $conflicts = $this->findConflicts($doctorId, $date, $duration, $ignoreId);
        if ($conflicts->isNotEmpty()) {
            throw new AppointmentConflictException(
                'الطبيب لديه موعد آخر في نفس الفترة الزمنية.',
                $conflicts->map->toArray()->all(),
            );
        }
    }

    /**
     * @return Collection<int, Appointment>
     */
    protected function findConflicts(int $doctorId, Carbon $date, int $duration, ?int $ignoreId = null): Collection
    {
        $start = $date->copy();
        $end = $start->copy()->addMinutes($duration);
        $buffer = self::CONFLICT_WINDOW_MINUTES;

        $windowStart = $start->copy()->subMinutes($buffer);
        $windowEnd = $end->copy()->addMinutes($buffer);

        $query = Appointment::query()
            ->where('doctor_id', $doctorId)
            ->whereIn('status', [
                AppointmentStatus::Scheduled->value,
                AppointmentStatus::Completed->value,
                AppointmentStatus::ConvertedToVisit->value,
            ])
            ->whereBetween('appointment_date', [$windowStart, $windowEnd]);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->get()->filter(function (Appointment $other) use ($start, $end) {
            $otherStart = $other->appointment_date;
            $otherEnd = $otherStart->copy()->addMinutes($other->duration_minutes ?? self::DEFAULT_DURATION_MINUTES);
            return $otherStart < $end && $otherEnd > $start;
        })->values();
    }
}
