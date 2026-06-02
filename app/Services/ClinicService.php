<?php

namespace App\Services;

use App\Enums\ClinicStatus;
use App\Events\ClinicArchived;
use App\Events\ClinicCreated;
use App\Events\ClinicStatusChanged;
use App\Events\ClinicUpdated;
use App\Exceptions\ClinicOperationException;
use App\Models\Clinic;
use App\Models\ClinicWorkingHour;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ClinicService
{
    public function paginate(array $filters, int $perPage = 20, ?User $viewer = null): LengthAwarePaginator
    {
        $query = Clinic::query()->with('workingHours')->withCount(['users', 'visits', 'appointments']);

        if ($viewer && $viewer->isDoctor()) {
            $query->where('id', $viewer->clinic_id);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['status'])) {
            $query->withStatus($filters['status']);
        }

        if (isset($filters['active_only']) && $filters['active_only']) {
            $query->active();
        }

        if (!empty($filters['min_doctors'])) {
            $min = (int) $filters['min_doctors'];
            $query->has('users', '>=', $min);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function listActive(): Collection
    {
        return Clinic::active()
            ->with('workingHours')
            ->orderBy('name')
            ->get(['id', 'name', 'status'])
            ->map(fn (Clinic $clinic) => [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'status' => $clinic->status?->value,
                'accepts_appointments' => $clinic->acceptsAppointments(),
                'working_hours' => $clinic->workingHoursSchedule(),
            ]);
    }

    public function createClinic(array $data, ?User $actor = null): Clinic
    {
        return DB::transaction(function () use ($data, $actor) {
            $clinic = Clinic::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'location' => $data['location'] ?? null,
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? ClinicStatus::Active->value,
            ]);
            $this->syncWorkingHours($clinic, $data['working_hours'] ?? []);
            event(new ClinicCreated($clinic, $actor));
            return $clinic->fresh('workingHours');
        });
    }

    public function updateClinic(Clinic $clinic, array $data, ?User $actor = null): Clinic
    {
        $previousStatus = $clinic->status;
        $clinic->fill([
            'name' => $data['name'] ?? $clinic->name,
            'description' => array_key_exists('description', $data) ? $data['description'] : $clinic->description,
            'location' => array_key_exists('location', $data) ? $data['location'] : $clinic->location,
            'phone' => array_key_exists('phone', $data) ? $data['phone'] : $clinic->phone,
        ]);

        if (isset($data['status'])) {
            $newStatus = ClinicStatus::from($data['status']);
            if ($newStatus === ClinicStatus::Archived) {
                throw new ClinicOperationException(
                    'استخدم endpoint الأرشفة المخصص (archive) لأرشفة العيادة.',
                    ['clinic_id' => $clinic->id],
                );
            }
            $clinic->status = $newStatus;
        }

        $clinic->save();
        if (array_key_exists('working_hours', $data)) {
            $this->syncWorkingHours($clinic, $data['working_hours'] ?? []);
        }
        event(new ClinicUpdated($clinic, $actor));

        if (isset($data['status']) && $previousStatus !== $clinic->status) {
            event(new ClinicStatusChanged($clinic, $previousStatus, $actor));
        }

        return $clinic->fresh('workingHours');
    }

    public function archiveClinic(Clinic $clinic, ?User $actor = null): Clinic
    {
        if ($clinic->hasActiveDoctors()) {
            throw new ClinicOperationException(
                'لا يمكن أرشفة عيادة يوجد بها أطباء نشطون. قم بإعادة تعيين الأطباء أولاً.',
                [
                    'clinic_id' => $clinic->id,
                    'doctors_count' => $clinic->getDoctorsCountAttribute(),
                ],
            );
        }

        $previousStatus = $clinic->status;
        $clinic->status = ClinicStatus::Archived;
        $clinic->archived_at = now();
        $clinic->save();

        event(new ClinicStatusChanged($clinic, $previousStatus, $actor));
        event(new ClinicArchived($clinic, $actor));

        return $clinic;
    }

    public function activateClinic(Clinic $clinic, ?User $actor = null): Clinic
    {
        $previousStatus = $clinic->status;
        $clinic->status = ClinicStatus::Active;
        $clinic->archived_at = null;
        $clinic->save();

        event(new ClinicStatusChanged($clinic, $previousStatus, $actor));
        event(new ClinicUpdated($clinic, $actor));

        return $clinic;
    }

    public function deactivateClinic(Clinic $clinic, ?User $actor = null): Clinic
    {
        $previousStatus = $clinic->status;
        $clinic->status = ClinicStatus::Inactive;
        $clinic->save();

        event(new ClinicStatusChanged($clinic, $previousStatus, $actor));
        event(new ClinicUpdated($clinic, $actor));

        return $clinic;
    }

    public function deleteClinic(Clinic $clinic, ?User $actor = null): void
    {
        if ($clinic->hasVisits()) {
            throw new ClinicOperationException(
                'لا يمكن حذف عيادة لها سجل زيارات. قم بأرشفتها بدلاً من ذلك.',
                ['clinic_id' => $clinic->id, 'visits_count' => $clinic->getVisitsCountAttribute()],
            );
        }
        if ($clinic->appointments()->exists()) {
            throw new ClinicOperationException(
                'لا يمكن حذف عيادة لها مواعيد مسجلة. قم بأرشفتها بدلاً من ذلك.',
                ['clinic_id' => $clinic->id, 'appointments_count' => $clinic->getAppointmentsCountAttribute()],
            );
        }
        if ($clinic->doctors()->exists()) {
            throw new ClinicOperationException(
                'لا يمكن حذف عيادة مرتبط بها أطباء. قم بإعادة تعيين الأطباء أولاً.',
                ['clinic_id' => $clinic->id],
            );
        }
        $clinic->delete();
    }

    public function assignDoctor(Clinic $clinic, int $doctorId, ?User $actor = null): User
    {
        $doctor = User::where('id', $doctorId)->where('role', 'doctor')->first();
        if (!$doctor) {
            throw new ClinicOperationException('الطبيب غير موجود أو ليس بدور طبيب.');
        }
        if (!$clinic->acceptsAppointments()) {
            throw new ClinicOperationException(
                'لا يمكن إضافة طبيب لعيادة غير نشطة.',
                ['clinic_id' => $clinic->id, 'status' => $clinic->status->value],
            );
        }
        $doctor->clinic_id = $clinic->id;
        $doctor->save();
        event(new ClinicUpdated($clinic, $actor));
        return $doctor;
    }

    public function unassignDoctor(Clinic $clinic, int $doctorId, ?User $actor = null): User
    {
        $doctor = User::where('id', $doctorId)
            ->where('clinic_id', $clinic->id)
            ->where('role', 'doctor')
            ->first();
        if (!$doctor) {
            throw new ClinicOperationException('الطبيب غير موجود في هذه العيادة.');
        }
        $doctor->clinic_id = null;
        $doctor->save();
        event(new ClinicUpdated($clinic, $actor));
        return $doctor;
    }

    public function getStatistics(Clinic $clinic): array
    {
        $visits = $clinic->visits()->with('procedures')->get();
        $todayVisits = $visits->where('visit_date', today()->toDateString());
        $monthVisits = $visits->filter(fn ($v) => Carbon::parse($v->visit_date)->isCurrentMonth());
        $yearVisits = $visits->filter(fn ($v) => Carbon::parse($v->visit_date)->isCurrentYear());

        $visitsService = app(VisitService::class);
        $monthRevenue = 0;
        $centerMonth = 0;
        foreach ($monthVisits as $v) {
            $monthRevenue += (float) $v->amount_received;
            $totals = $visitsService->computeVisitTotals($v);
            $centerMonth += $totals['center_share'];
        }

        return [
            'doctors_count' => $clinic->getDoctorsCountAttribute(),
            'patients_count' => $clinic->getPatientsCountAttribute(),
            'visits_count' => $clinic->getVisitsCountAttribute(),
            'appointments_count' => $clinic->getAppointmentsCountAttribute(),
            'visits_today' => $todayVisits->count(),
            'visits_this_month' => $monthVisits->count(),
            'examinations_this_month' => $monthVisits->where('visit_type', 'examination')->count(),
            'reviews_this_month' => $monthVisits->where('visit_type', 'review')->count(),
            'revenue_today' => round((float) $todayVisits->sum('amount_received'), 2),
            'monthly_revenue' => round($monthRevenue, 2),
            'yearly_revenue' => round((float) $yearVisits->sum('amount_received'), 2),
            'center_monthly_share' => round($centerMonth, 2),
        ];
    }

    public function getDetailedReport(Clinic $clinic, int $recentLimit = 5): array
    {
        $doctors = $clinic->users()
            ->where('role', 'doctor')
            ->withCount('visits')
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'full_name' => $d->full_name,
                'email' => $d->email,
                'visits_count' => $d->visits_count,
                'examination_fee' => (float) $d->examination_fee,
            ]);

        $recentVisits = $clinic->visits()
            ->with(['patient', 'doctor', 'clinic'])
            ->latest('visit_date')
            ->limit($recentLimit)
            ->get()
            ->map(fn ($v) => [
                'id' => $v->id,
                'visit_date' => $v->visit_date->toDateString(),
                'visit_type' => $v->visit_type->value ?? $v->visit_type,
                'patient_name' => $v->patient->full_name,
                'doctor_name' => $v->doctor->full_name,
                'amount_received' => (float) $v->amount_received,
            ]);

        $recentAppointments = $clinic->appointments()
            ->with(['patient', 'doctor'])
            ->where('appointment_date', '>=', now()->subDays(30))
            ->orderBy('appointment_date', 'desc')
            ->limit($recentLimit)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'appointment_date' => $a->appointment_date->toDateTimeString(),
                'status' => $a->status->value ?? $a->status,
                'patient_name' => $a->patient->full_name,
                'doctor_name' => $a->doctor->full_name,
            ]);

        $recentPatients = $clinic->visits()
            ->with('patient')
            ->latest('visit_date')
            ->limit($recentLimit)
            ->get()
            ->pluck('patient')
            ->unique('id')
            ->values()
            ->map(fn ($p) => [
                'id' => $p->id,
                'full_name' => $p->full_name,
                'file_number' => $p->file_number,
                'phone' => $p->phone,
            ]);

        return [
            'statistics' => $this->getStatistics($clinic),
            'doctors' => $doctors,
            'recent_visits' => $recentVisits,
            'recent_appointments' => $recentAppointments,
            'recent_patients' => $recentPatients,
        ];
    }

    protected function syncWorkingHours(Clinic $clinic, array $workingHours): void
    {
        $byDay = collect($workingHours)->keyBy('day_of_week');

        foreach (ClinicWorkingHour::DAYS as $day) {
            $payload = $byDay->get($day, []);
            $isActive = filter_var($payload['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $clinic->workingHours()->updateOrCreate(
                ['day_of_week' => $day],
                [
                    'is_active' => $isActive,
                    'start_time' => $isActive ? ($payload['start_time'] ?? '09:00') : null,
                    'end_time' => $isActive ? ($payload['end_time'] ?? '17:00') : null,
                ],
            );
        }
    }
}
