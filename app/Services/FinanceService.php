<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\DoctorDeduction;
use App\Models\User;
use App\Models\Visit;

class FinanceService
{
    protected VisitService $visitService;

    public function __construct(VisitService $visitService)
    {
        $this->visitService = $visitService;
    }

    public function summary(): array
    {
        $visits = Visit::with('procedures')->get();

        $totalExaminationFees = $visits->sum('examination_fee');
        $totalAmountReceived = $visits->sum('amount_received');
        $totalComplexDiscount = $visits->sum('complex_discount');
        $totalDoctorDiscount = $visits->sum('doctor_discount');

        $totalDoctorProcedures = 0;
        $totalCenterProcedures = 0;

        foreach ($visits as $visit) {
            $totalDoctorProcedures += $visit->procedures->sum('doctor_fee');
            $totalCenterProcedures += $visit->procedures->sum('center_fee');
        }

        $totalDoctorShare = 0;
        $totalCenterShare = 0;

        foreach ($visits as $visit) {
            $totals = $this->visitService->computeVisitTotals($visit);
            $totalDoctorShare += $totals['doctor_share'];
            $totalCenterShare += $totals['center_share'];
        }

        $totalDeductions = DoctorDeduction::sum('amount');

        return [
            'total_examination_fees' => round($totalExaminationFees, 2),
            'total_amount_received' => round($totalAmountReceived, 2),
            'total_complex_discount' => round($totalComplexDiscount, 2),
            'total_doctor_discount' => round($totalDoctorDiscount, 2),
            'total_doctor_procedures' => round($totalDoctorProcedures, 2),
            'total_center_procedures' => round($totalCenterProcedures, 2),
            'total_doctor_share' => round($totalDoctorShare, 2),
            'total_center_share' => round($totalCenterShare, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_doctor_payable' => round($totalDoctorShare - $totalDeductions, 2),
        ];
    }

    public function doctorSummaries(): array
    {
        $doctors = User::where('role', UserRole::Doctor)->with('clinic')->get();

        return $doctors->map(function (User $doctor) {
            $visits = Visit::where('doctor_id', $doctor->id)->with('procedures')->get();

            $doctorShare = 0;
            foreach ($visits as $visit) {
                $totals = $this->visitService->computeVisitTotals($visit);
                $doctorShare += $totals['doctor_share'];
            }

            $deductions = DoctorDeduction::where('doctor_id', $doctor->id)->sum('amount');

            return [
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->full_name,
                'clinic' => $doctor->clinic?->name,
                'total_visits' => $visits->count(),
                'doctor_share' => round($doctorShare, 2),
                'deductions' => round($deductions, 2),
                'net_payable' => round($doctorShare - $deductions, 2),
            ];
        })->toArray();
    }
}
