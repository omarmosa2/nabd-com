<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
    ) {}

    public function patients(): JsonResponse
    {
        return response()->json($this->reportService->patientReport());
    }

    public function visits(): JsonResponse
    {
        return response()->json($this->reportService->visitReport());
    }

    public function finance(): JsonResponse
    {
        return response()->json($this->reportService->financeReport());
    }

    public function daily(Request $request): JsonResponse
    {
        $date = $request->get('date', now()->toDateString());
        return response()->json($this->reportService->dailyReport($date));
    }

    public function monthly(Request $request): JsonResponse
    {
        $month = $request->get('month', now()->format('Y-m'));
        return response()->json($this->reportService->monthlyReport($month));
    }
}
