<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Models\Patient;
use App\Services\VisitService;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        if (Patient::count() > 0) {
            return;
        }

        $visitService = app(VisitService::class);

        $patients = [
            ['full_name' => 'محمد عبدالله', 'age' => 35, 'gender' => Gender::Male, 'residence' => 'الرياض', 'phone' => '0501234567'],
            ['full_name' => 'فاطمة أحمد', 'age' => 28, 'gender' => Gender::Female, 'residence' => 'جدة', 'phone' => '0502345678'],
            ['full_name' => 'علي حسين', 'age' => 45, 'gender' => Gender::Male, 'residence' => 'الدمام', 'phone' => '0503456789'],
            ['full_name' => 'نورة سعد', 'age' => 22, 'gender' => Gender::Female, 'residence' => 'مكة', 'phone' => '0504567890'],
            ['full_name' => 'خالد عمر', 'age' => 50, 'gender' => Gender::Male, 'residence' => 'المدينة', 'phone' => '0505678901'],
            ['full_name' => 'سارة محمد', 'age' => 30, 'gender' => Gender::Female, 'residence' => 'الرياض', 'phone' => '0506789012'],
            ['full_name' => 'يوسف إبراهيم', 'age' => 40, 'gender' => Gender::Male, 'residence' => 'جدة', 'phone' => '0507890123'],
            ['full_name' => 'مريم خالد', 'age' => 25, 'gender' => Gender::Female, 'residence' => 'الدمام', 'phone' => '0508901234'],
            ['full_name' => 'عبدالرحمن فهد', 'age' => 55, 'gender' => Gender::Male, 'residence' => 'مكة', 'phone' => '0509012345'],
            ['full_name' => 'هند سلطان', 'age' => 33, 'gender' => Gender::Female, 'residence' => 'المدينة', 'phone' => '0510123456'],
            ['full_name' => 'سلطان ناصر', 'age' => 42, 'gender' => Gender::Male, 'residence' => 'الرياض', 'phone' => '0511234567'],
            ['full_name' => 'لمى عبدالعزيز', 'age' => 27, 'gender' => Gender::Female, 'residence' => 'جدة', 'phone' => '0512345678'],
            ['full_name' => 'بندر سلمان', 'age' => 38, 'gender' => Gender::Male, 'residence' => 'الدمام', 'phone' => '0513456789'],
            ['full_name' => 'ريم فيصل', 'age' => 29, 'gender' => Gender::Female, 'residence' => 'مكة', 'phone' => '0514567890'],
            ['full_name' => 'تركي ماجد', 'age' => 48, 'gender' => Gender::Male, 'residence' => 'المدينة', 'phone' => '0515678901'],
            ['full_name' => 'دانة وليد', 'age' => 24, 'gender' => Gender::Female, 'residence' => 'الرياض', 'phone' => '0516789012'],
            ['full_name' => 'ماجد عادل', 'age' => 36, 'gender' => Gender::Male, 'residence' => 'جدة', 'phone' => '0517890123'],
            ['full_name' => 'عائشة كريم', 'age' => 31, 'gender' => Gender::Female, 'residence' => 'الدمام', 'phone' => '0518901234'],
            ['full_name' => 'فهد راشد', 'age' => 44, 'gender' => Gender::Male, 'residence' => 'مكة', 'phone' => '0519012345'],
            ['full_name' => 'جواهر حمد', 'age' => 26, 'gender' => Gender::Female, 'residence' => 'المدينة', 'phone' => '0520123456'],
        ];

        foreach ($patients as $patient) {
            Patient::create([
                'file_number' => $visitService->generateFileNumber(),
                'full_name' => $patient['full_name'],
                'age' => $patient['age'],
                'gender' => $patient['gender'],
                'residence' => $patient['residence'],
                'phone' => $patient['phone'],
            ]);
        }
    }
}
