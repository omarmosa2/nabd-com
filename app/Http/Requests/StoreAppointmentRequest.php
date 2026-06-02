<?php

namespace App\Http\Requests;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) return false;
        return $user->isAdmin() || $user->isReception() || $user->isDoctor();
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:users,id'],
            'clinic_id' => ['required', 'exists:clinics,id'],
            'appointment_date' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
            'status' => ['nullable', Rule::enum(AppointmentStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'يجب اختيار المريض.',
            'doctor_id.required' => 'يجب اختيار الطبيب.',
            'clinic_id.required' => 'يجب اختيار العيادة.',
            'appointment_date.required' => 'يجب تحديد تاريخ ووقت الموعد.',
            'appointment_date.date' => 'صيغة التاريخ غير صحيحة.',
        ];
    }
}
