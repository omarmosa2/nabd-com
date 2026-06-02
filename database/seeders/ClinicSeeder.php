<?php

namespace Database\Seeders;

use App\Models\Clinic;
use Illuminate\Database\Seeder;

class ClinicSeeder extends Seeder
{
    public function run(): void
    {
        $clinics = [
            ['name' => 'عيادة الأسنان'],
            ['name' => 'عيادة العيون'],
            ['name' => 'عيادة الباطنية'],
        ];

        foreach ($clinics as $clinic) {
            Clinic::updateOrCreate(
                ['name' => $clinic['name']],
                $clinic
            );
        }
    }
}
