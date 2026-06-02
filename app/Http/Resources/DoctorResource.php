<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $doctor = $this->resource;
        return [
            'id' => $doctor->id,
            'full_name' => $doctor->full_name,
            'email' => $doctor->email,
            'phone' => $doctor->phone,
            'role' => $doctor->role?->value,
            'clinic_id' => $doctor->clinic_id,
            'clinic' => $doctor->clinic ? [
                'id' => $doctor->clinic->id,
                'name' => $doctor->clinic->name,
                'status' => $doctor->clinic->status?->value,
            ] : null,
            'specialization' => $doctor->specialization,
            'examination_fee' => (float) $doctor->examination_fee,
            'percentage_type' => $doctor->percentage_type?->value,
            'percentage_value' => (float) $doctor->percentage_value,
            'is_active' => (bool) $doctor->is_active,
            'is_archived' => $doctor->isArchived(),
            'archived_at' => $doctor->archived_at?->toDateTimeString(),
            'notes' => $doctor->notes,
            'created_at' => $doctor->created_at?->toDateTimeString(),

            'visits_count' => (int) ($doctor->visits_count ?? 0),
            'appointments_count' => (int) ($doctor->appointments_count ?? 0),
            'deductions_count' => (int) ($doctor->deductions_count ?? 0),
            'patients_count' => (int) $doctor->getPatientsCountAttribute(),
            'monthly_revenue' => (float) $doctor->getMonthlyRevenueAttribute(),
            'today_revenue' => (float) $doctor->getTodayRevenueAttribute(),
            'yearly_revenue' => (float) $doctor->getYearlyRevenueAttribute(),
            'net_earnings' => (float) $doctor->getNetEarningsAttribute(),

            'accepts_appointments' => $doctor->acceptsAppointments(),
            'accepts_visits' => $doctor->acceptsVisits(),
        ];
    }
}
