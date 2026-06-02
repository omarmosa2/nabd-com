<?php

namespace App\Http\Requests\Concerns;

use App\Models\ClinicWorkingHour;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesClinicWorkingHours
{
    protected function workingHoursRules(): array
    {
        return [
            'working_hours' => ['nullable', 'array', 'max:7'],
            'working_hours.*.day_of_week' => ['required_with:working_hours', 'string', Rule::in(ClinicWorkingHour::DAYS), 'distinct'],
            'working_hours.*.is_active' => ['required_with:working_hours', 'boolean'],
            'working_hours.*.start_time' => ['nullable', 'date_format:H:i'],
            'working_hours.*.end_time' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (!$this->has('working_hours')) {
                return;
            }

            $workingHours = $this->input('working_hours', []);
            if (!is_array($workingHours)) {
                return;
            }

            foreach ($workingHours as $index => $day) {
                if (!is_array($day)) {
                    continue;
                }

                $isActive = filter_var($day['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $start = $day['start_time'] ?? null;
                $end = $day['end_time'] ?? null;

                if ($isActive === true) {
                    if ($this->isBlankTime($start)) {
                        $validator->errors()->add("working_hours.{$index}.start_time", 'وقت بداية الدوام مطلوب عند تفعيل اليوم.');
                    }
                    if ($this->isBlankTime($end)) {
                        $validator->errors()->add("working_hours.{$index}.end_time", 'وقت نهاية الدوام مطلوب عند تفعيل اليوم.');
                    }
                    if (!$this->isBlankTime($start) && !$this->isBlankTime($end) && $end <= $start) {
                        $validator->errors()->add("working_hours.{$index}.end_time", 'وقت نهاية الدوام يجب أن يكون بعد وقت البداية.');
                    }
                } else {
                    if (!$this->isBlankTime($start)) {
                        $validator->errors()->add("working_hours.{$index}.start_time", 'وقت البداية يجب أن يكون فارغاً عند تعطيل اليوم.');
                    }
                    if (!$this->isBlankTime($end)) {
                        $validator->errors()->add("working_hours.{$index}.end_time", 'وقت النهاية يجب أن يكون فارغاً عند تعطيل اليوم.');
                    }
                }
            }
        });
    }

    protected function isBlankTime(mixed $value): bool
    {
        return $value === null || $value === '';
    }
}
