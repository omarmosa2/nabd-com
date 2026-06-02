<?php

namespace App\Http\Requests;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) return false;
        if ($user->isAdmin() || $user->isReception()) {
            return true;
        }
        if ($user->isDoctor()) {
            return $this->route('appointment')?->doctor_id === $user->id;
        }
        return false;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['sometimes', 'exists:patients,id'],
            'doctor_id' => ['sometimes', 'exists:users,id'],
            'clinic_id' => ['sometimes', 'exists:clinics,id'],
            'appointment_date' => ['sometimes', 'date'],
            'duration_minutes' => ['sometimes', 'integer', 'min:5', 'max:480'],
            'status' => ['sometimes', Rule::enum(AppointmentStatus::class)],
            'cancel_reason' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
