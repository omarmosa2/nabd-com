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

class DoctorManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite extension is required for in-memory database tests.');
        }

        parent::setUp();
    }

    public function test_admin_can_create_doctor_account(): void
    {
        Sanctum::actingAs($this->admin());

        $clinic = Clinic::create(['name' => 'عيادة الأطباء', 'status' => 'active']);

        $response = $this->postJson('/api/doctors', [
            'full_name' => 'د. طبيب جديد',
            'email' => 'new-doctor@example.com',
            'password' => 'secret123',
            'phone' => '0501112222',
            'clinic_id' => $clinic->id,
            'specialization' => 'طب الأسنان',
            'examination_fee' => 150,
            'percentage_type' => 'fixed',
            'percentage_value' => 0,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.full_name', 'د. طبيب جديد')
            ->assertJsonPath('data.role', UserRole::Doctor->value)
            ->assertJsonPath('data.clinic_id', $clinic->id)
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('users', [
            'email' => 'new-doctor@example.com',
            'role' => UserRole::Doctor->value,
            'clinic_id' => $clinic->id,
        ]);
    }

    public function test_appointment_cannot_use_doctor_from_another_clinic(): void
    {
        Sanctum::actingAs($this->admin());

        $doctorClinic = Clinic::create(['name' => 'عيادة الطبيب', 'status' => 'active']);
        $otherClinic = Clinic::create(['name' => 'عيادة أخرى', 'status' => 'active']);

        $doctor = User::create([
            'full_name' => 'د. عيادة محددة',
            'email' => 'doctor-clinic-guard@example.com',
            'password' => 'password',
            'role' => UserRole::Doctor->value,
            'clinic_id' => $doctorClinic->id,
            'is_active' => true,
        ]);

        $patient = Patient::create([
            'file_number' => 'P-DOC-001',
            'full_name' => 'مريض اختبار',
            'age' => 30,
            'gender' => Gender::Male->value,
            'phone' => '0503334444',
        ]);

        $this->postJson('/api/appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $otherClinic->id,
            'appointment_date' => '2026-06-06 10:00',
            'duration_minutes' => 30,
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'الطبيب لا ينتمي إلى العيادة المحددة.');
    }

    protected function admin(): User
    {
        return User::create([
            'full_name' => 'مدير اختبار الأطباء',
            'email' => 'admin-doctors@example.com',
            'password' => 'password',
            'role' => UserRole::Admin->value,
        ]);
    }
}
