<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Patient::class);
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'min:0', 'max:150'],
            'gender' => ['required', 'in:male,female'],
            'residence' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ];
    }
}
