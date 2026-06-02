<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\UserRole;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClinicWorkingHoursTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite extension is required for in-memory database tests.');
        }

        parent::setUp();
    }

    public function test_admin_can_store_clinic_working_hours(): void
    {
        Sanctum::actingAs($this->admin());

        $response = $this->postJson('/api/clinics', [
            'name' => 'عيادة الاختبار',
            'status' => 'active',
            'working_hours' => [
                [
                    'day_of_week' => 'saturday',
                    'is_active' => true,
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.working_hours.0.day_of_week', 'saturday')
            ->assertJsonPath('data.working_hours.0.is_active', true)
            ->assertJsonPath('data.working_hours.0.start_time', '09:00')
            ->assertJsonPath('data.working_hours.0.end_time', '17:00')
            ->assertJsonPath('data.working_hours.6.day_of_week', 'friday')
            ->assertJsonPath('data.working_hours.6.is_active', false);

        $this->assertDatabaseHas('clinic_working_hours', [
            'day_of_week' => 'saturday',
            'is_active' => true,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);
    }

    public function test_appointments_must_be_inside_clinic_working_hours(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        $clinic = Clinic::create(['name' => 'عيادة المواعيد', 'status' => 'active']);
        $clinic->workingHours()->createMany([
            ['day_of_week' => 'saturday', 'is_active' => true, 'start_time' => '09:00', 'end_time' => '17:00'],
            ['day_of_week' => 'sunday', 'is_active' => false],
        ]);

        $doctor = User::create([
            'full_name' => 'د. اختبار',
            'email' => 'doctor-working-hours@example.com',
            'password' => 'password',
            'role' => UserRole::Doctor->value,
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);

        $patient = Patient::create([
            'file_number' => 'P-001',
            'full_name' => 'مريض اختبار',
            'age' => 30,
            'gender' => Gender::Male->value,
            'phone' => '0500000000',
        ]);

        $basePayload = [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'duration_minutes' => 30,
        ];

        $this->postJson('/api/appointments', $basePayload + [
            'appointment_date' => '2026-06-06 08:30',
        ])->assertStatus(422);

        $this->postJson('/api/appointments', $basePayload + [
            'appointment_date' => '2026-06-06 10:00',
        ])->assertCreated();
    }

    protected function admin(): User
    {
        return User::create([
            'full_name' => 'مدير الاختبار',
            'email' => 'admin-working-hours@example.com',
            'password' => 'password',
            'role' => UserRole::Admin->value,
        ]);
    }
}
