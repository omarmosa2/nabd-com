<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    protected VisitService $visitService;

    public function __construct(VisitService $visitService)
    {
        $this->visitService = $visitService;
    }

    public function getStats(): array
    {
        $today = now()->toDateString();

        return [
            'patients_today' => Patient::whereDate('created_at', $today)->count(),
            'examinations_today' => Visit::whereDate('visit_date', $today)
                ->where('visit_type', 'examination')
                ->count(),
            'reviews_today' => Visit::whereDate('visit_date', $today)
                ->where('visit_type', 'review')
                ->count(),
            'revenue_today' => Visit::whereDate('visit_date', $today)
                ->sum('amount_received'),
            'appointments_today' => Appointment::whereDate('appointment_date', $today)
                ->whereIn('status', [AppointmentStatus::Scheduled->value, AppointmentStatus::Completed->value])
                ->count(),
            'appointments_upcoming' => Appointment::where('appointment_date', '>=', now())
                ->where('appointment_date', '<=', now()->addHours(24))
                ->where('status', AppointmentStatus::Scheduled->value)
                ->count(),
        ];
    }

    public function getRevenue(): array
    {
        $currentMonth = now()->startOfMonth();

        $visits = Visit::where('visit_date', '>=', $currentMonth)
            ->with('procedures')
            ->get();

        $monthlyTotal = $visits->sum('amount_received');
        $complexShare = 0;
        $doctorsShare = 0;

        foreach ($visits as $visit) {
            $totals = $this->visitService->computeVisitTotals($visit);
            $complexShare += $totals['center_share'];
            $doctorsShare += $totals['doctor_share'];
        }

        return [
            'monthly_total' => round($monthlyTotal, 2),
            'complex_share' => round($complexShare, 2),
            'doctors_share' => round($doctorsShare, 2),
        ];
    }

    public function getUpcomingAppointments(): array
    {
        return Appointment::where('appointment_date', '>=', now())
            ->where('appointment_date', '<=', now()->addHours(24))
            ->where('status', AppointmentStatus::Scheduled->value)
            ->with(['patient', 'doctor', 'clinic'])
            ->orderBy('appointment_date')
            ->get()
            ->map(fn ($apt) => [
                'id' => $apt->id,
                'patient_name' => $apt->patient->full_name,
                'patient_file_number' => $apt->patient->file_number,
                'patient_phone' => $apt->patient->phone,
                'doctor_name' => $apt->doctor->full_name,
                'clinic_name' => $apt->clinic->name,
                'appointment_date' => $apt->appointment_date->toDateTimeString(),
                'duration_minutes' => $apt->duration_minutes,
                'status' => $apt->status->value,
                'notes' => $apt->notes,
            ])
            ->toArray();
    }

    public function getTodayScheduleByDoctor(): array
    {
        return Appointment::with(['patient', 'doctor', 'clinic'])
            ->whereDate('appointment_date', today())
            ->whereIn('status', [
                AppointmentStatus::Scheduled->value,
                AppointmentStatus::Completed->value,
                AppointmentStatus::ConvertedToVisit->value,
            ])
            ->orderBy('appointment_date')
            ->get()
            ->groupBy('doctor_id')
            ->map(function ($items, $doctorId) {
                $doctor = $items->first()->doctor;
                return [
                    'doctor_id' => (int) $doctorId,
                    'doctor_name' => $doctor->full_name,
                    'clinic_name' => $doctor->clinic?->name,
                    'count' => $items->count(),
                    'appointments' => $items->map(fn ($a) => [
                        'id' => $a->id,
                        'patient_name' => $a->patient->full_name,
                        'appointment_date' => $a->appointment_date->toDateTimeString(),
                        'status' => $a->status->value,
                        'status_label' => $a->status->label(),
                    ])->values()->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function getTopDoctors(): array
    {
        return User::where('role', 'doctor')
            ->whereNull('archived_at')
            ->with('clinic')
            ->withCount(['visits', 'appointments'])
            ->get()
            ->map(function ($doc) {
                $revenue = (float) $doc->visits()
                    ->where('visit_date', '>=', now()->startOfMonth())
                    ->sum('examination_fee');
                $patients = $doc->visits()->distinct('patient_id')->count('patient_id');
                return [
                    'id' => $doc->id,
                    'full_name' => $doc->full_name,
                    'specialization' => $doc->specialization,
                    'clinic_id' => $doc->clinic_id,
                    'clinic_name' => $doc->clinic?->name,
                    'is_active' => (bool) $doc->is_active,
                    'visits_count' => (int) $doc->visits_count,
                    'appointments_count' => (int) $doc->appointments_count,
                    'patients_count' => $patients,
                    'monthly_revenue' => round($revenue, 2),
                ];
            })
            ->sortByDesc('visits_count')
            ->take(5)
            ->values()
            ->toArray();
    }

    public function getTopClinics(): array
    {
        $clinics = Clinic::query()
            ->where('status', '!=', 'archived')
            ->withCount(['visits', 'users', 'appointments'])
            ->get();

        return $clinics
            ->sortByDesc(fn ($c) => $c->visits_count)
            ->take(5)
            ->values()
            ->map(function ($c) {
                $revenue = (float) $c->visits()->where('visit_date', '>=', now()->startOfMonth())->sum('amount_received');
                $patientsCount = $c->visits()->distinct('patient_id')->count('patient_id');
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'visits_count' => $c->visits_count,
                    'appointments_count' => $c->appointments_count,
                    'doctors_count' => $c->users_count,
                    'patients_count' => $patientsCount,
                    'monthly_revenue' => round($revenue, 2),
                    'status' => $c->status?->value,
                ];
            })
            ->toArray();
    }

    public function getClinicStatsSummary(): array
    {
        $totalClinics = Clinic::count();
        $activeClinics = Clinic::where('status', 'active')->count();
        $inactiveClinics = Clinic::where('status', 'inactive')->count();
        $archivedClinics = Clinic::where('status', 'archived')->count();
        $totalDoctors = User::where('role', 'doctor')->whereNotNull('clinic_id')->count();
        $totalClinicRevenue = (float) \App\Models\Visit::where('visit_date', '>=', now()->startOfMonth())->sum('amount_received');

        $topByVisits = $this->getClinicRanking('visits_count', 5);
        $topByRevenue = $this->getClinicRanking('monthly_revenue', 5);
        $topByPatients = $this->getClinicRanking('patients_count', 5);

        return [
            'total_clinics' => $totalClinics,
            'active_clinics' => $activeClinics,
            'inactive_clinics' => $inactiveClinics,
            'archived_clinics' => $archivedClinics,
            'doctors_in_clinics' => $totalDoctors,
            'total_clinic_revenue' => round($totalClinicRevenue, 2),
            'top_by_visits' => $topByVisits,
            'top_by_revenue' => $topByRevenue,
            'top_by_patients' => $topByPatients,
        ];
    }

    public function getDoctorStatsSummary(): array
    {
        $totalDoctors = User::where('role', 'doctor')->whereNull('archived_at')->count();
        $activeDoctors = User::where('role', 'doctor')->where('is_active', true)->whereNull('archived_at')->count();
        $inactiveDoctors = User::where('role', 'doctor')->where('is_active', false)->whereNull('archived_at')->count();
        $archivedDoctors = User::where('role', 'doctor')->whereNotNull('archived_at')->count();

        $visitsThisMonth = Visit::where('visit_date', '>=', now()->startOfMonth())->count();
        $appointmentsToday = Appointment::whereDate('appointment_date', today())
            ->whereIn('status', [AppointmentStatus::Scheduled->value, AppointmentStatus::Completed->value])
            ->count();
        $doctorsRevenue = (float) Visit::where('visit_date', '>=', now()->startOfMonth())->sum('examination_fee');
        $patientsTotal = Patient::count();

        $topByVisits = collect($this->getTopDoctors())->sortByDesc('visits_count')->take(5)->values()->toArray();
        $topByRevenue = collect($this->getTopDoctors())->sortByDesc('monthly_revenue')->take(5)->values()->toArray();
        $topByPatients = User::where('role', 'doctor')
            ->whereNull('archived_at')
            ->with('clinic')
            ->get()
            ->map(function ($d) {
                $patients = $d->visits()->distinct('patient_id')->count('patient_id');
                $revenue = (float) $d->visits()->where('visit_date', '>=', now()->startOfMonth())->sum('examination_fee');
                return [
                    'id' => $d->id,
                    'full_name' => $d->full_name,
                    'clinic_id' => $d->clinic_id,
                    'clinic_name' => $d->clinic?->name,
                    'specialization' => $d->specialization,
                    'visits_count' => $d->visits()->count(),
                    'patients_count' => $patients,
                    'monthly_revenue' => round($revenue, 2),
                ];
            })
            ->sortByDesc('patients_count')
            ->take(5)
            ->values()
            ->toArray();

        return [
            'total_doctors' => $totalDoctors,
            'active_doctors' => $activeDoctors,
            'inactive_doctors' => $inactiveDoctors,
            'archived_doctors' => $archivedDoctors,
            'visits_this_month' => $visitsThisMonth,
            'appointments_today' => $appointmentsToday,
            'doctors_revenue' => round($doctorsRevenue, 2),
            'total_patients' => $patientsTotal,
            'top_by_visits' => $topByVisits,
            'top_by_revenue' => $topByRevenue,
            'top_by_patients' => $topByPatients,
        ];
    }

    protected function getClinicRanking(string $sortBy, int $limit): array
    {
        $clinics = Clinic::query()
            ->where('status', 'active')
            ->withCount(['visits', 'users', 'appointments'])
            ->get();

        return $clinics
            ->map(function ($c) {
                $revenue = (float) $c->visits()->where('visit_date', '>=', now()->startOfMonth())->sum('amount_received');
                $patientsCount = $c->visits()->distinct('patient_id')->count('patient_id');
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'visits_count' => $c->visits_count,
                    'appointments_count' => $c->appointments_count,
                    'doctors_count' => $c->users_count,
                    'patients_count' => $patientsCount,
                    'monthly_revenue' => round($revenue, 2),
                ];
            })
            ->sortByDesc($sortBy)
            ->take($limit)
            ->values()
            ->toArray();
    }

    public function getChartsData(): array
    {
        $sixMonthsAgo = now()->subMonths(6);

        $visitsByMonth = Visit::where('visit_date', '>=', $sixMonthsAgo)
            ->select(
                DB::raw('DATE_FORMAT(visit_date, "%Y-%m") as month'),
                DB::raw('SUM(CASE WHEN visit_type = "examination" THEN 1 ELSE 0 END) as examinations'),
                DB::raw('SUM(CASE WHEN visit_type = "review" THEN 1 ELSE 0 END) as reviews')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $revenueByMonth = Visit::where('visit_date', '>=', $sixMonthsAgo)
            ->select(
                DB::raw('DATE_FORMAT(visit_date, "%Y-%m") as month'),
                DB::raw('SUM(amount_received) as revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $patientsByClinic = Visit::join('clinics', 'visits.clinic_id', '=', 'clinics.id')
            ->select('clinics.name', DB::raw('COUNT(DISTINCT visits.patient_id) as patient_count'))
            ->groupBy('clinics.id', 'clinics.name')
            ->get();

        $appointmentsByStatus = Appointment::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->status => $row->count])
            ->toArray();

        return [
            'visits_by_month' => $visitsByMonth,
            'revenue_by_month' => $revenueByMonth,
            'patients_by_clinic' => $patientsByClinic,
            'appointments_by_status' => $appointmentsByStatus,
        ];
    }
}
