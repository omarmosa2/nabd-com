<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Visit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    protected VisitService $visitService;

    public function __construct(VisitService $visitService)
    {
        $this->visitService = $visitService;
    }

    public function patientReport(): array
    {
        return [
            'total_patients' => Patient::count(),
            'new_this_month' => Patient::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'by_gender' => Patient::select('gender', DB::raw('count(*) as count'))
                ->groupBy('gender')
                ->pluck('count', 'gender')
                ->toArray(),
            'top_patients' => Patient::withCount('visits')
                ->orderByDesc('visits_count')
                ->take(10)
                ->get(['id', 'file_number', 'full_name', 'phone'])
                ->toArray(),
        ];
    }

    public function visitReport(): array
    {
        return [
            'total_visits' => Visit::count(),
            'this_month' => Visit::whereMonth('visit_date', now()->month)
                ->whereYear('visit_date', now()->year)
                ->count(),
            'by_type' => Visit::select('visit_type', DB::raw('count(*) as count'))
                ->groupBy('visit_type')
                ->pluck('count', 'visit_type')
                ->toArray(),
            'free_reviews' => Visit::where('is_free_review', true)->count(),
            'by_clinic' => Visit::join('clinics', 'visits.clinic_id', '=', 'clinics.id')
                ->select('clinics.name', DB::raw('count(*) as count'))
                ->groupBy('clinics.name')
                ->pluck('count', 'name')
                ->toArray(),
        ];
    }

    public function financeReport(): array
    {
        $financeService = app(FinanceService::class);

        return [
            'summary' => $financeService->summary(),
            'doctors' => $financeService->doctorSummaries(),
        ];
    }

    public function dailyReport(string $date): array
    {
        $visits = Visit::whereDate('visit_date', $date)
            ->with(['patient', 'doctor', 'clinic', 'procedures'])
            ->get();

        $totalRevenue = $visits->sum('amount_received');
        $totalExaminations = $visits->where('visit_type', 'examination')->count();
        $totalReviews = $visits->where('visit_type', 'review')->count();

        $doctorBreakdown = [];
        foreach ($visits as $visit) {
            $doctorId = $visit->doctor_id;
            if (!isset($doctorBreakdown[$doctorId])) {
                $doctorBreakdown[$doctorId] = [
                    'doctor_name' => $visit->doctor->full_name,
                    'visits_count' => 0,
                    'revenue' => 0,
                ];
            }
            $doctorBreakdown[$doctorId]['visits_count']++;
            $doctorBreakdown[$doctorId]['revenue'] += $visit->amount_received;
        }

        return [
            'date' => $date,
            'total_visits' => $visits->count(),
            'total_revenue' => round($totalRevenue, 2),
            'examinations' => $totalExaminations,
            'reviews' => $totalReviews,
            'doctor_breakdown' => array_values($doctorBreakdown),
        ];
    }

    public function monthlyReport(string $month): array
    {
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $visits = Visit::whereBetween('visit_date', [$startDate, $endDate])
            ->with(['patient', 'doctor', 'clinic', 'procedures'])
            ->get();

        $totalRevenue = $visits->sum('amount_received');
        $totalExaminations = $visits->where('visit_type', 'examination')->count();
        $totalReviews = $visits->where('visit_type', 'review')->count();

        $dailyBreakdown = $visits->groupBy(function($visit) {
            return $visit->visit_date->format('Y-m-d');
        })->map(function($dayVisits, $date) {
            return [
                'date' => $date,
                'visits_count' => $dayVisits->count(),
                'revenue' => round($dayVisits->sum('amount_received'), 2),
            ];
        })->values();

        $doctorBreakdown = [];
        foreach ($visits as $visit) {
            $doctorId = $visit->doctor_id;
            if (!isset($doctorBreakdown[$doctorId])) {
                $doctorBreakdown[$doctorId] = [
                    'doctor_name' => $visit->doctor->full_name,
                    'visits_count' => 0,
                    'revenue' => 0,
                ];
            }
            $doctorBreakdown[$doctorId]['visits_count']++;
            $doctorBreakdown[$doctorId]['revenue'] += $visit->amount_received;
        }

        return [
            'month' => $month,
            'total_visits' => $visits->count(),
            'total_revenue' => round($totalRevenue, 2),
            'examinations' => $totalExaminations,
            'reviews' => $totalReviews,
            'daily_breakdown' => $dailyBreakdown,
            'doctor_breakdown' => array_values($doctorBreakdown),
        ];
    }
}
