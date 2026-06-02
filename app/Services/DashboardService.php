<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
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
            ->with(['patient', 'doctor', 'clinic'])
            ->orderBy('appointment_date')
            ->get()
            ->map(fn($apt) => [
                'id' => $apt->id,
                'patient_name' => $apt->patient->full_name,
                'doctor_name' => $apt->doctor->full_name,
                'clinic_name' => $apt->clinic->name,
                'appointment_date' => $apt->appointment_date->toDateTimeString(),
                'notes' => $apt->notes,
            ])
            ->toArray();
    }

    public function getTopDoctors(): array
    {
        return User::where('role', 'doctor')
            ->withCount('visits')
            ->orderByDesc('visits_count')
            ->take(5)
            ->get(['id', 'full_name', 'clinic_id', 'visits_count'])
            ->map(fn($doc) => [
                'id' => $doc->id,
                'full_name' => $doc->full_name,
                'clinic' => $doc->clinic?->name,
                'visits_count' => $doc->visits_count,
            ])
            ->toArray();
    }

    public function getTopClinics(): array
    {
        return Clinic::withCount('visits')
            ->orderByDesc('visits_count')
            ->get(['id', 'name', 'visits_count'])
            ->map(fn($clinic) => [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'visits_count' => $clinic->visits_count,
            ])
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

        return [
            'visits_by_month' => $visitsByMonth,
            'revenue_by_month' => $revenueByMonth,
            'patients_by_clinic' => $patientsByClinic,
        ];
    }
}
