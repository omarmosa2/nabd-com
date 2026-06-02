<?php

namespace App\Http\Requests;

use App\Services\AppointmentService;
use Illuminate\Foundation\Http\FormRequest;

class CheckAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['required', 'exists:users,id'],
            'appointment_date' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
            'ignore_id' => ['nullable', 'integer', 'exists:appointments,id'],
        ];
    }

    public function duration(): int
    {
        return (int) ($this->input('duration_minutes') ?? AppointmentService::DEFAULT_DURATION_MINUTES);
    }
}
