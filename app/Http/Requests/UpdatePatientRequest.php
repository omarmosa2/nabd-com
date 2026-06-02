<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('patient'));
    }

    public function rules(): array
    {
        return [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'age' => ['sometimes', 'integer', 'min:0', 'max:150'],
            'gender' => ['sometimes', 'in:male,female'],
            'residence' => ['nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20'],
        ];
    }
}
