<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string'],
        ];
    }
}
