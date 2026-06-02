<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClinicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $counts = $this->whenLoaded('counts', fn () => null);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'location' => $this->location,
            'phone' => $this->phone,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'accepts_appointments' => $this->acceptsAppointments(),
            'accepts_visits' => $this->acceptsVisits(),
            'working_hours' => $this->workingHoursSchedule(),
            'is_archived' => $this->isArchived(),
            'has_active_doctors' => $this->hasActiveDoctors(),
            'has_visits' => $this->hasVisits(),
            'archived_at' => $this->archived_at?->toDateTimeString(),
            'doctors_count' => $this->whenCounted('users', fn () => $this->users()->where('role', 'doctor')->count()),
            'visits_count' => $this->whenCounted('visits'),
            'appointments_count' => $this->whenCounted('appointments'),
            'patients_count' => $this->getPatientsCountAttribute(),
            'monthly_revenue' => $this->getMonthlyRevenueAttribute(),
            'today_revenue' => $this->getTodayRevenueAttribute(),
            'yearly_revenue' => $this->getYearlyRevenueAttribute(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
