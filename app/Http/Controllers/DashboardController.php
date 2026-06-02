<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->dashboardService->getStats());
    }

    public function revenue(): JsonResponse
    {
        return response()->json($this->dashboardService->getRevenue());
    }

    public function appointments(): JsonResponse
    {
        return response()->json([
            'appointments' => $this->dashboardService->getUpcomingAppointments()
        ]);
    }

    public function topDoctors(): JsonResponse
    {
        return response()->json([
            'doctors' => $this->dashboardService->getTopDoctors()
        ]);
    }

    public function topClinics(): JsonResponse
    {
        return response()->json([
            'clinics' => $this->dashboardService->getTopClinics()
        ]);
    }

    public function charts(): JsonResponse
    {
        return response()->json($this->dashboardService->getChartsData());
    }
}
