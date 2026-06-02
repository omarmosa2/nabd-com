<?php

namespace App\Services;

use App\Enums\ClinicStatus;
use App\Enums\DeductionType;
use App\Enums\UserRole;
use App\Exceptions\DoctorOperationException;
use App\Models\Clinic;
use App\Models\DoctorDeduction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoctorService
{
    public function paginate(array $filters, int $perPage = 20, ?User $viewer = null): LengthAwarePaginator
    {
        $query = User::query()
            ->doctors()
            ->with('clinic')
            ->withCount([
                'visits',
                'appointments',
                'deductions',
            ])
            ->search($filters['search'] ?? null)
            ->inClinic(isset($filters['clinic_id']) ? (int) $filters['clinic_id'] : null)
            ->withSpecialization($filters['specialization'] ?? null)
            ->activeFlag($filters['active'] ?? null);

        if (!empty($filters['archived_only'])) {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }

        if ($viewer && $viewer->isDoctor()) {
            $query->where('id', $viewer->id);
        }

        return $query->orderByDesc('is_active')
            ->orderBy('full_name')
            ->paginate($perPage);
    }

    public function listActive(?int $clinicId = null): array
    {
        return User::query()
            ->activeDoctors()
            ->inClinic($clinicId)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'email', 'clinic_id', 'specialization', 'examination_fee'])
            ->toArray();
    }

    public function createDoctor(array $data, ?User $actor = null): User
    {
        $data['role'] = UserRole::Doctor->value;
        $data['is_active'] = $data['is_active'] ?? true;

        $clinic = Clinic::find($data['clinic_id'] ?? null);
        if (!$clinic) {
            throw new DoctorOperationException('العيادة غير موجودة.', ['clinic_id']);
        }
        if ($clinic->status === ClinicStatus::Archived) {
            throw new DoctorOperationException('لا يمكن إضافة طبيب لعيادة مؤرشفة.', ['clinic_id']);
        }

        return DB::transaction(function () use ($data, $actor) {
            $doctor = User::create([
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => UserRole::Doctor->value,
                'clinic_id' => $data['clinic_id'],
                'phone' => $data['phone'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'examination_fee' => $data['examination_fee'] ?? 0,
                'percentage_type' => $data['percentage_type'] ?? 'fixed',
                'percentage_value' => $data['percentage_value'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
                'notes' => $data['notes'] ?? null,
            ]);
            $doctor->load('clinic');

            event(new \App\Events\DoctorCreated($doctor, $actor));
            return $doctor;
        });
    }

    public function updateDoctor(User $doctor, array $data, ?User $actor = null): User
    {
        if (!empty($data['clinic_id'])) {
            $clinic = Clinic::find($data['clinic_id']);
            if (!$clinic) {
                throw new DoctorOperationException('العيادة غير موجودة.', ['clinic_id']);
            }
            if ($clinic->status === ClinicStatus::Archived) {
                throw new DoctorOperationException('لا يمكن نقل طبيب إلى عيادة مؤرشفة.', ['clinic_id']);
            }
        }

        return DB::transaction(function () use ($doctor, $data, $actor) {
            $update = [
                'full_name' => $data['full_name'] ?? $doctor->full_name,
                'email' => $data['email'] ?? $doctor->email,
                'phone' => $data['phone'] ?? $doctor->phone,
                'clinic_id' => $data['clinic_id'] ?? $doctor->clinic_id,
                'specialization' => $data['specialization'] ?? $doctor->specialization,
                'examination_fee' => $data['examination_fee'] ?? $doctor->examination_fee,
                'percentage_type' => $data['percentage_type'] ?? $doctor->percentage_type->value,
                'percentage_value' => $data['percentage_value'] ?? $doctor->percentage_value,
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $doctor->is_active,
                'notes' => $data['notes'] ?? $doctor->notes,
            ];

            if (!empty($data['password'])) {
                $update['password'] = Hash::make($data['password']);
            }

            $doctor->update($update);
            $doctor->load('clinic');

            event(new \App\Events\DoctorUpdated($doctor, $actor));
            return $doctor;
        });
    }

    public function archiveDoctor(User $doctor, ?User $actor = null): User
    {
        if ($doctor->visits()->exists()) {
            throw new DoctorOperationException(
                'لا يمكن أرشفة طبيب لديه زيارات. استخدم إلغاء التفعيل بدلاً من ذلك.',
                ['doctor_id'],
                ['visits_count' => $doctor->visits()->count()]
            );
        }

        return DB::transaction(function () use ($doctor, $actor) {
            $doctor->update(['archived_at' => now()]);
            event(new \App\Events\DoctorArchived($doctor, $actor));
            return $doctor->fresh('clinic');
        });
    }

    public function activateDoctor(User $doctor, ?User $actor = null): User
    {
        $doctor->update(['is_active' => true]);
        event(new \App\Events\DoctorUpdated($doctor, $actor));
        return $doctor->fresh('clinic');
    }

    public function deactivateDoctor(User $doctor, ?User $actor = null): User
    {
        $doctor->update(['is_active' => false]);
        event(new \App\Events\DoctorUpdated($doctor, $actor));
        return $doctor->fresh('clinic');
    }

    public function getStatistics(User $doctor): array
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfDay = $now->copy()->startOfDay();

        $visitsTotal = $doctor->visits()->count();
        $examinations = $doctor->visits()->where('visit_type', 'examination')->count();
        $reviews = $doctor->visits()->where('visit_type', 'review')->count();
        $freeReviews = $doctor->visits()->where('visit_type', 'review')->where('is_free_review', true)->count();

        $patientsTotal = $doctor->visits()->distinct('patient_id')->count('patient_id');
        $newPatientsThisMonth = $doctor->visits()
            ->where('visit_date', '>=', $startOfMonth)
            ->whereNotIn('patient_id', function ($q) use ($doctor, $startOfMonth) {
                $q->select('patient_id')->from('visits')
                    ->where('doctor_id', $doctor->id)
                    ->where('visit_date', '<', $startOfMonth);
            })
            ->distinct('patient_id')
            ->count('patient_id');

        $returningPatients = $patientsTotal - $newPatientsThisMonth;

        $appointmentsTotal = $doctor->appointments()->count();
        $todayAppointments = $doctor->appointments()
            ->whereDate('appointment_date', $now->toDateString())
            ->count();
        $upcomingAppointments = $doctor->appointments()
            ->where('appointment_date', '>=', $now)
            ->whereIn('status', ['scheduled'])
            ->count();
        $missedAppointments = $doctor->appointments()
            ->where('status', 'missed')
            ->count();

        $examsRevenue = (float) $doctor->visits()->where('visit_type', 'examination')->sum('examination_fee');
        $procsRevenue = (float) \App\Models\Procedure::whereIn('visit_id', $doctor->visits()->select('id'))->sum('doctor_fee');
        $deductions = (float) $doctor->deductions()->whereIn('type', ['deduction', 'advance', 'other'])->sum('amount');
        $bonuses = (float) $doctor->deductions()->where('type', 'bonus')->sum('amount');
        $netEarnings = $examsRevenue + $procsRevenue - $deductions + $bonuses;

        $procsCount = \App\Models\Procedure::whereIn('visit_id', $doctor->visits()->select('id'))->count();
        $procsTotalRevenue = (float) \App\Models\Procedure::whereIn('visit_id', $doctor->visits()->select('id'))->sum('doctor_fee');

        $monthlyVisits = $doctor->visits()
            ->whereYear('visit_date', $now->year)
            ->whereMonth('visit_date', $now->month)
            ->count();
        $monthlyRevenue = (float) $doctor->visits()
            ->whereYear('visit_date', $now->year)
            ->whereMonth('visit_date', $now->month)
            ->sum('examination_fee');

        $todayRevenue = (float) $doctor->visits()
            ->whereDate('visit_date', $now->toDateString())
            ->sum('examination_fee');

        return [
            'doctor_id' => $doctor->id,
            'patients' => [
                'total' => $patientsTotal,
                'new_this_month' => $newPatientsThisMonth,
                'returning' => max(0, $returningPatients),
            ],
            'visits' => [
                'total' => $visitsTotal,
                'examinations' => $examinations,
                'reviews' => $reviews,
                'free_reviews' => $freeReviews,
                'monthly' => $monthlyVisits,
                'today' => $doctor->visits()->whereDate('visit_date', $now->toDateString())->count(),
            ],
            'appointments' => [
                'total' => $appointmentsTotal,
                'today' => $todayAppointments,
                'upcoming' => $upcomingAppointments,
                'missed' => $missedAppointments,
            ],
            'procedures' => [
                'count' => $procsCount,
                'revenue' => $procsTotalRevenue,
            ],
            'finance' => [
                'examination_revenue' => $examsRevenue,
                'procedure_revenue' => $procsRevenue,
                'deductions' => $deductions,
                'bonuses' => $bonuses,
                'net_earnings' => $netEarnings,
                'monthly_revenue' => $monthlyRevenue,
                'today_revenue' => $todayRevenue,
            ],
        ];
    }

    public function getFinance(User $doctor, ?string $from = null, ?string $to = null): array
    {
        $now = now();
        $from = $from ? Carbon::parse($from)->startOfDay() : $now->copy()->startOfMonth();
        $to = $to ? Carbon::parse($to)->endOfDay() : $now->copy()->endOfDay();

        $visitsQ = $doctor->visits()->whereBetween('visit_date', [$from, $to]);
        $examsRevenue = (float) (clone $visitsQ)->where('visit_type', 'examination')->sum('examination_fee');
        $reviewsRevenue = (float) (clone $visitsQ)->where('visit_type', 'review')->sum('examination_fee');

        $procRevenue = (float) \App\Models\Procedure::whereIn('visit_id', $visitsQ->select('id'))->sum('doctor_fee');

        $deductions = $doctor->deductions()
            ->whereBetween('deduction_date', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('deduction_date')
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'amount' => (float) $d->amount,
                'reason' => $d->reason,
                'type' => $d->type->value,
                'type_label' => $d->type->label(),
                'date' => $d->deduction_date?->toDateString(),
                'created_at' => $d->created_at?->toDateTimeString(),
            ])->toArray();

        $deductionsTotal = (float) collect($deductions)
            ->whereIn('type', ['deduction', 'advance', 'other'])
            ->sum('amount');
        $bonusesTotal = (float) collect($deductions)->where('type', 'bonus')->sum('amount');

        $gross = $examsRevenue + $reviewsRevenue + $procRevenue;
        $net = $gross - $deductionsTotal + $bonusesTotal;

        return [
            'doctor_id' => $doctor->id,
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'examination_revenue' => $examsRevenue,
            'review_revenue' => $reviewsRevenue,
            'procedure_revenue' => $procRevenue,
            'gross_revenue' => $gross,
            'deductions' => $deductions,
            'deductions_total' => $deductionsTotal,
            'bonuses_total' => $bonusesTotal,
            'net_earnings' => $net,
        ];
    }

    public function getSchedule(User $doctor, string $range = 'week', ?string $date = null): array
    {
        $now = now();
        $anchor = $date ? Carbon::parse($date) : $now->copy();

        switch ($range) {
            case 'day':
                $start = $anchor->copy()->startOfDay();
                $end = $anchor->copy()->endOfDay();
                break;
            case 'month':
                $start = $anchor->copy()->startOfMonth();
                $end = $anchor->copy()->endOfMonth();
                break;
            case 'week':
            default:
                $start = $anchor->copy()->startOfWeek();
                $end = $anchor->copy()->endOfWeek();
                break;
        }

        $appointments = $doctor->appointments()
            ->with(['patient:id,file_number,full_name,phone', 'clinic:id,name'])
            ->whereBetween('appointment_date', [$start, $end])
            ->orderBy('appointment_date')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'date' => $a->appointment_date->toDateString(),
                'time' => $a->appointment_date->format('H:i'),
                'duration_minutes' => $a->duration_minutes,
                'status' => $a->status->value,
                'status_label' => $a->status->label(),
                'patient_id' => $a->patient_id,
                'patient_name' => $a->patient?->full_name,
                'patient_file_number' => $a->patient?->file_number,
                'clinic_id' => $a->clinic_id,
                'clinic_name' => $a->clinic?->name,
                'notes' => $a->notes,
            ])
            ->toArray();

        $booked = count(array_filter($appointments, fn($a) => in_array($a['status'], ['scheduled', 'completed'])));
        $missed = count(array_filter($appointments, fn($a) => $a['status'] === 'missed'));
        $cancelled = count(array_filter($appointments, fn($a) => $a['status'] === 'cancelled'));
        $upcoming = count(array_filter($appointments, fn($a) => $a['status'] === 'scheduled' && Carbon::parse($a['date'] . ' ' . $a['time'])->isFuture()));

        return [
            'doctor_id' => $doctor->id,
            'range' => $range,
            'period' => [
                'from' => $start->toDateString(),
                'to' => $end->toDateString(),
            ],
            'total_slots' => count($appointments),
            'booked_slots' => $booked,
            'available_slots' => max(0, (8 * 5) - $booked),
            'upcoming_appointments' => $upcoming,
            'missed_appointments' => $missed,
            'cancelled_appointments' => $cancelled,
            'appointments' => $appointments,
        ];
    }

    public function addDeduction(User $doctor, array $data, ?User $actor = null): DoctorDeduction
    {
        $type = $data['type'] ?? DeductionType::Deduction->value;
        $deduction = DoctorDeduction::create([
            'doctor_id' => $doctor->id,
            'amount' => $data['amount'],
            'reason' => $data['reason'],
            'type' => $type,
            'deduction_date' => $data['deduction_date'] ?? now()->toDateString(),
        ]);

        event(new \App\Events\DeductionAdded($deduction, $actor));
        return $deduction;
    }

    public function getPatients(User $doctor, int $limit = 20): array
    {
        return $doctor->visits()
            ->with('patient:id,file_number,full_name,phone,gender')
            ->selectRaw('patient_id, MAX(visit_date) as last_visit, COUNT(*) as visits_count')
            ->groupBy('patient_id')
            ->orderByDesc('last_visit')
            ->limit($limit)
            ->get()
            ->map(fn($v) => [
                'patient_id' => $v->patient_id,
                'file_number' => $v->patient?->file_number,
                'full_name' => $v->patient?->full_name,
                'phone' => $v->patient?->phone,
                'gender' => $v->patient?->gender?->value,
                'visits_count' => (int) $v->visits_count,
                'last_visit' => $v->last_visit,
            ])
            ->toArray();
    }

    public function getSpecializations(): array
    {
        return User::query()
            ->doctors()
            ->whereNotNull('specialization')
            ->where('specialization', '!=', '')
            ->distinct()
            ->orderBy('specialization')
            ->pluck('specialization')
            ->filter()
            ->values()
            ->toArray();
    }
}
