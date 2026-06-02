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
            ],
            'doctor' => [
                'id' => $this->doctor->id,
                'full_name' => $this->doctor->full_name,
            ],
            'clinic' => [
                'id' => $this->clinic->id,
                'name' => $this->clinic->name,
            ],
            'appointment_date' => $this->appointment_date?->toDateTimeString(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
