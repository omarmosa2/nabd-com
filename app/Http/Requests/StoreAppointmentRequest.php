<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:users,id'],
            'clinic_id' => ['required', 'exists:clinics,id'],
            'appointment_date' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
