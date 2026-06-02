<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreDeductionRequest;
use App\Models\DoctorDeduction;
use App\Models\User;
use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function __construct(
        protected FinanceService $financeService,
    ) {}

    public function summary(): JsonResponse
    {
        return response()->json($this->financeService->summary());
    }

    public function doctors(): JsonResponse
    {
        return response()->json([
            'data' => $this->financeService->doctorSummaries(),
        ]);
    }

    public function storeDeduction(StoreDeductionRequest $request): JsonResponse
    {
        $deduction = DoctorDeduction::create($request->validated());

        return response()->json([
            'message' => 'Deduction created successfully',
            'data' => [
                'id' => $deduction->id,
                'doctor_id' => $deduction->doctor_id,
                'amount' => $deduction->amount,
                'reason' => $deduction->reason,
                'created_at' => $deduction->created_at->toDateTimeString(),
            ],
        ], 201);
    }

    public function doctorDetails(int $doctorId): JsonResponse
    {
        $doctor = User::where('role', UserRole::Doctor)->findOrFail($doctorId);

        $visits = \App\Models\Visit::where('doctor_id', $doctorId)
            ->with('procedures')
            ->get();

        $visitService = app(\App\Services\VisitService::class);

        $totalShare = 0;
        $visitDetails = [];

        foreach ($visits as $visit) {
            $totals = $visitService->computeVisitTotals($visit);
            $totalShare += $totals['doctor_share'];

            $visitDetails[] = [
                'visit_id' => $visit->id,
                'date' => $visit->visit_date->toDateString(),
                'patient' => $visit->patient->full_name,
                'type' => $visit->visit_type->value,
                'examination_fee' => $visit->examination_fee,
                'procedures_count' => $visit->procedures->count(),
                'doctor_share' => $totals['doctor_share'],
            ];
        }

        $deductions = DoctorDeduction::where('doctor_id', $doctorId)->get();
        $totalDeductions = $deductions->sum('amount');

        return response()->json([
            'doctor' => [
                'id' => $doctor->id,
                'full_name' => $doctor->full_name,
                'clinic' => $doctor->clinic?->name,
            ],
            'total_visits' => $visits->count(),
            'total_share' => round($totalShare, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_payable' => round($totalShare - $totalDeductions, 2),
            'visits' => $visitDetails,
            'deductions' => $deductions->map(fn($d) => [
                'id' => $d->id,
                'amount' => $d->amount,
                'reason' => $d->reason,
                'date' => $d->created_at->toDateString(),
            ]),
        ]);
    }
}
