<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Visit::class);
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:users,id'],
            'clinic_id' => ['required', 'exists:clinics,id'],
            'visit_date' => ['required', 'date'],
            'visit_type' => ['required', 'in:examination,review'],
            'examination_fee' => ['nullable', 'numeric', 'min:0'],
            'amount_received' => ['nullable', 'numeric', 'min:0'],
            'complex_discount' => ['nullable', 'numeric', 'min:0'],
            'doctor_discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'procedures' => ['nullable', 'array'],
            'procedures.*.name' => ['required_with:procedures', 'string'],
            'procedures.*.center_fee' => ['required_with:procedures', 'numeric', 'min:0'],
            'procedures.*.doctor_fee' => ['required_with:procedures', 'numeric', 'min:0'],
        ];
    }
}
