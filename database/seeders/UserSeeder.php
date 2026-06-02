<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@nabd.com'],
            [
                'full_name' => 'مدير النظام',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'examination_fee' => 0,
            ]
        );

        $doctors = [
            ['full_name' => 'د. أحمد محمد', 'email' => 'doctor1@nabd.com', 'clinic_id' => 1, 'examination_fee' => 150],
            ['full_name' => 'د. سارة علي', 'email' => 'doctor2@nabd.com', 'clinic_id' => 1, 'examination_fee' => 150],
            ['full_name' => 'د. خالد حسن', 'email' => 'doctor3@nabd.com', 'clinic_id' => 2, 'examination_fee' => 200],
            ['full_name' => 'د. فاطمة يوسف', 'email' => 'doctor4@nabd.com', 'clinic_id' => 2, 'examination_fee' => 200],
            ['full_name' => 'د. عمر إبراهيم', 'email' => 'doctor5@nabd.com', 'clinic_id' => 3, 'examination_fee' => 100],
        ];

        foreach ($doctors as $doctor) {
            User::updateOrCreate(
                ['email' => $doctor['email']],
                [
                    'full_name' => $doctor['full_name'],
                    'password' => Hash::make('password'),
                    'role' => UserRole::Doctor,
                    'clinic_id' => $doctor['clinic_id'],
                    'examination_fee' => $doctor['examination_fee'],
                ]
            );
        }

        User::updateOrCreate(
            ['email' => 'reception1@nabd.com'],
            [
                'full_name' => 'موظف الاستقبال الأول',
                'password' => Hash::make('password'),
                'role' => UserRole::Reception,
                'examination_fee' => 0,
            ]
        );

        User::updateOrCreate(
            ['email' => 'reception2@nabd.com'],
            [
                'full_name' => 'موظف الاستقبال الثاني',
                'password' => Hash::make('password'),
                'role' => UserRole::Reception,
                'examination_fee' => 0,
            ]
        );
    }
}
