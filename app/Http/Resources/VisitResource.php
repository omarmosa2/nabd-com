<?php

namespace App\Http\Resources;

use App\Services\VisitService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totals = app(VisitService::class)->computeVisitTotals($this->resource);

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
            'visit_date' => $this->visit_date?->toDateString(),
            'visit_type' => $this->visit_type,
            'is_free_review' => $this->is_free_review,
            'examination_fee' => $this->examination_fee,
            'amount_received' => $this->amount_received,
            'complex_discount' => $this->complex_discount,
            'doctor_discount' => $this->doctor_discount,
            'notes' => $this->notes,
            'procedures' => ProcedureResource::collection($this->whenLoaded('procedures')),
            'totals' => $totals,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
