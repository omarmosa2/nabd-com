<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'role' => $this->role,
            'clinic' => $this->whenLoaded('clinic', fn () => [
                'id' => $this->clinic->id,
                'name' => $this->clinic->name,
            ]),
            'examination_fee' => $this->examination_fee,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
