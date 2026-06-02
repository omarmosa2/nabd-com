<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('visit'));
    }

    public function rules(): array
    {
        return [
            'visit_date' => ['sometimes', 'date'],
            'visit_type' => ['sometimes', 'in:examination,review'],
            'examination_fee' => ['sometimes', 'numeric', 'min:0'],
            'amount_received' => ['sometimes', 'numeric', 'min:0'],
            'complex_discount' => ['sometimes', 'numeric', 'min:0'],
            'doctor_discount' => ['sometimes', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'procedures' => ['nullable', 'array'],
            'procedures.*.name' => ['required_with:procedures', 'string'],
            'procedures.*.center_fee' => ['required_with:procedures', 'numeric', 'min:0'],
            'procedures.*.doctor_fee' => ['required_with:procedures', 'numeric', 'min:0'],
        ];
    }
}
