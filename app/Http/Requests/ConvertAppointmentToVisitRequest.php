<?php

namespace App\Http\Requests;

use App\Enums\VisitType;
use Illuminate\Foundation\Http\FormRequest;

class ConvertAppointmentToVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->isAdmin() || $user->isReception());
    }

    public function rules(): array
    {
        return [
            'visit_type' => ['nullable', 'in:examination,review'],
            'visit_date' => ['nullable', 'date'],
            'examination_fee' => ['nullable', 'numeric', 'min:0'],
            'amount_received' => ['nullable', 'numeric', 'min:0'],
            'complex_discount' => ['nullable', 'numeric', 'min:0'],
            'doctor_discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
