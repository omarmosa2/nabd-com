<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Carbon;

class VisitService
{
    public function computeVisitTotals(Visit $visit): array
    {
        $visit->load('procedures');

        $procedures = $visit->procedures;
        $doctorProcedures = $procedures->sum('doctor_fee');
        $centerProcedures = $procedures->sum('center_fee');

        $doctorExamShare = $visit->examination_fee - $visit->doctor_discount - $visit->complex_discount;
        $centerExam = $visit->complex_discount;

        $totalFees = $visit->examination_fee + $doctorProcedures + $centerProcedures;
        $doctorShare = $doctorExamShare + $doctorProcedures;
        $centerShare = $centerExam + $centerProcedures;

        return [
            'total_fees' => round($totalFees, 2),
            'doctor_share' => round($doctorShare, 2),
            'center_share' => round($centerShare, 2),
            'doctor_exam_share' => round($doctorExamShare, 2),
            'center_exam' => round($centerExam, 2),
            'doctor_procedures' => round($doctorProcedures, 2),
            'center_procedures' => round($centerProcedures, 2),
        ];
    }

    public function isFreeReview(Patient $patient, User $doctor): bool
    {
        return $patient->visits()
            ->where('doctor_id', $doctor->id)
            ->where('visit_type', 'review')
            ->exists();
    }

    public function generateFileNumber(): string
    {
        $today = Carbon::now();
        $prefix = $today->format('Y-md');

        $count = Patient::whereDate('created_at', $today)
            ->count();

        $nextNumber = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$nextNumber}";
    }
}
