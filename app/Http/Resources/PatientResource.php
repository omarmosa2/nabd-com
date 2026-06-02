<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_number' => $this->file_number,
            'full_name' => $this->full_name,
            'age' => $this->age,
            'gender' => $this->gender,
            'residence' => $this->residence,
            'phone' => $this->phone,
            'visits_count' => $this->whenCounted('visits'),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
