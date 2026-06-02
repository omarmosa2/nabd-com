<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient' => [
                'id' => $this->patient->id,
                'file_number' => $this->patient->file_number,
                'full_name' => $this->patient->full_name,
                'phone' => $this->patient->phone,
            ],
            'doctor' => [
                'id' => $this->doctor->id,
                'full_name' => $this->doctor->full_name,
                'clinic_id' => $this->doctor->clinic_id,
            ],
            'clinic' => [
                'id' => $this->clinic->id,
                'name' => $this->clinic->name,
            ],
            'appointment_date' => $this->appointment_date?->toDateTimeString(),
            'end_time' => $this->appointment_date
                ? $this->appointment_date->copy()->addMinutes($this->duration_minutes ?? 30)->toDateTimeString()
                : null,
            'duration_minutes' => $this->duration_minutes ?? 30,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'is_terminal' => $this->status?->isTerminal(),
            'can_be_cancelled' => $this->canBeCancelled(),
            'can_be_converted' => $this->canBeConverted(),
            'is_past' => $this->isPast(),
            'is_upcoming' => $this->isUpcoming(),
            'notes' => $this->notes,
            'cancel_reason' => $this->cancel_reason,
            'cancelled_at' => $this->cancelled_at?->toDateTimeString(),
            'completed_at' => $this->completed_at?->toDateTimeString(),
            'visit_id' => $this->visit_id,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
