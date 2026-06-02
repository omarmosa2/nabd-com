<?php

namespace App\Http\Requests;

use App\Enums\ClinicStatus;
use App\Http\Requests\Concerns\ValidatesClinicWorkingHours;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClinicRequest extends FormRequest
{
    use ValidatesClinicWorkingHours;

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $clinicId = $this->route('clinic')?->id;
        return array_merge([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('clinics', 'name')->ignore($clinicId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['sometimes', Rule::enum(ClinicStatus::class)],
        ], $this->workingHoursRules());
    }
}
