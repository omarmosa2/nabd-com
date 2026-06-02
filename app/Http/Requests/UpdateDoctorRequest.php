<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $doctorId = $this->route('doctor');
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($doctorId)],
            'password' => ['nullable', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:32', Rule::unique('users', 'phone')->ignore($doctorId)],
            'clinic_id' => ['required', 'integer', 'exists:clinics,id'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'examination_fee' => ['required', 'numeric', 'min:0'],
            'percentage_type' => ['nullable', Rule::in(['fixed', 'percentage'])],
            'percentage_value' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'اسم الطبيب مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.unique' => 'البريد الإلكتروني مستخدم من قبل.',
            'phone.unique' => 'رقم الجوال مستخدم من قبل.',
            'clinic_id.exists' => 'العيادة المختارة غير موجودة.',
        ];
    }
}
