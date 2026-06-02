<?php

namespace Database\Seeders;

use App\Enums\VisitType;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class VisitSeeder extends Seeder
{
    public function run(): void
    {
        if (Visit::count() > 0) {
            return;
        }

        $visits = [
            [
                'patient_id' => 1,
                'doctor_id' => 2,
                'clinic_id' => 1,
                'visit_date' => now()->subDays(5),
                'visit_type' => VisitType::Examination,
                'examination_fee' => 150,
                'amount_received' => 150,
                'complex_discount' => 0,
                'doctor_discount' => 0,
                'notes' => 'فحص أولي',
                'procedures' => [
                    ['name' => 'تنظيف أسنان', 'center_fee' => 100, 'doctor_fee' => 80],
                ],
            ],
            [
                'patient_id' => 2,
                'doctor_id' => 4,
                'clinic_id' => 2,
                'visit_date' => now()->subDays(3),
                'visit_type' => VisitType::Examination,
                'examination_fee' => 200,
                'amount_received' => 200,
                'complex_discount' => 0,
                'doctor_discount' => 20,
                'notes' => 'فحص نظر',
                'procedures' => [
                    ['name' => 'فحص ضغط العين', 'center_fee' => 50, 'doctor_fee' => 40],
                ],
            ],
            [
                'patient_id' => 3,
                'doctor_id' => 6,
                'clinic_id' => 3,
                'visit_date' => now()->subDays(2),
                'visit_type' => VisitType::Examination,
                'examination_fee' => 100,
                'amount_received' => 100,
                'complex_discount' => 10,
                'doctor_discount' => 0,
                'notes' => null,
                'procedures' => [],
            ],
            [
                'patient_id' => 1,
                'doctor_id' => 2,
                'clinic_id' => 1,
                'visit_date' => now()->subDay(),
                'visit_type' => VisitType::Review,
                'examination_fee' => 150,
                'amount_received' => 0,
                'complex_discount' => 0,
                'doctor_discount' => 0,
                'is_free_review' => true,
                'notes' => 'مراجعة مجانية',
                'procedures' => [],
            ],
            [
                'patient_id' => 4,
                'doctor_id' => 3,
                'clinic_id' => 1,
                'visit_date' => now(),
                'visit_type' => VisitType::Examination,
                'examination_fee' => 150,
                'amount_received' => 150,
                'complex_discount' => 0,
                'doctor_discount' => 0,
                'notes' => null,
                'procedures' => [
                    ['name' => 'حشوة سن', 'center_fee' => 120, 'doctor_fee' => 100],
                    ['name' => 'أشعة بانوراما', 'center_fee' => 80, 'doctor_fee' => 30],
                ],
            ],
        ];

        foreach ($visits as $visitData) {
            $proceduresData = $visitData['procedures'];
            unset($visitData['procedures']);

            $visit = Visit::create($visitData);

            foreach ($proceduresData as $procedure) {
                $visit->procedures()->create($procedure);
            }
        }
    }
}
