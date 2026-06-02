<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorDeductionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $d = $this->resource;
        return [
            'id' => $d->id,
            'doctor_id' => $d->doctor_id,
            'amount' => (float) $d->amount,
            'reason' => $d->reason,
            'type' => $d->type?->value,
            'type_label' => $d->type?->label(),
            'affects' => $d->type?->affectsNet(),
            'deduction_date' => $d->deduction_date?->toDateString(),
            'created_at' => $d->created_at?->toDateTimeString(),
        ];
    }
}
