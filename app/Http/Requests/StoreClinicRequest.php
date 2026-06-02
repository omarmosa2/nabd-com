<?php

namespace App\Http\Requests;

use App\Enums\ClinicStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClinicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:clinics,name'],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', Rule::enum(ClinicStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم العيادة مطلوب.',
            'name.unique' => 'اسم العيادة موجود مسبقاً.',
            'name.max' => 'اسم العيادة طويل جداً.',
        ];
    }
}
