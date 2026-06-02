<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Visit::class);
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:users,id'],
            'clinic_id' => ['required', 'exists:clinics,id'],
            'visit_date' => ['required', 'date'],
            'visit_type' => ['required', 'in:examination,review'],
            'examination_fee' => ['nullable', 'numeric', 'min:0'],
            'amount_received' => ['nullable', 'numeric', 'min:0'],
            'complex_discount' => ['nullable', 'numeric', 'min:0'],
            'doctor_discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'procedures' => ['nullable', 'array'],
            'procedures.*.name' => ['required_with:procedures', 'string'],
            'procedures.*.center_fee' => ['required_with:procedures', 'numeric', 'min:0'],
            'procedures.*.doctor_fee' => ['required_with:procedures', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $clinicId = $this->input('clinic_id');
            if ($clinicId) {
                $clinic = \App\Models\Clinic::find($clinicId);
                if (!$clinic) {
                    $v->errors()->add('clinic_id', 'العيادة غير موجودة.');
                } elseif (!$clinic->acceptsVisits()) {
                    $v->errors()->add('clinic_id', 'العيادة غير نشطة ولا تستقبل زيارات جديدة.');
                }
            }

            $doctorId = $this->input('doctor_id');
            if ($doctorId) {
                $doctor = \App\Models\User::find($doctorId);
                if (!$doctor) {
                    $v->errors()->add('doctor_id', 'الطبيب غير موجود.');
                } elseif (!$doctor->isDoctor()) {
                    $v->errors()->add('doctor_id', 'المستخدم المحدد ليس طبيباً.');
                } elseif ($doctor->isArchived()) {
                    $v->errors()->add('doctor_id', 'الطبيب مؤرشف ولا يستقبل زيارات جديدة.');
                } elseif (!$doctor->is_active) {
                    $v->errors()->add('doctor_id', 'الطبيب غير مفعّل ولا يستقبل زيارات جديدة.');
                }
            }
        });
    }
}
