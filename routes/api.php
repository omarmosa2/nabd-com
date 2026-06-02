<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorDeductionController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Dashboard
    Route::prefix('dashboard')->middleware('role:admin')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/revenue', [DashboardController::class, 'revenue']);
        Route::get('/appointments', [DashboardController::class, 'appointments']);
        Route::get('/today-schedule', [DashboardController::class, 'todaySchedule']);
        Route::get('/top-doctors', [DashboardController::class, 'topDoctors']);
        Route::get('/top-clinics', [DashboardController::class, 'topClinics']);
        Route::get('/clinics', [DashboardController::class, 'clinics']);
        Route::get('/doctors', [DashboardController::class, 'doctors']);
        Route::get('/charts', [DashboardController::class, 'charts']);
    });

    // Reception
    Route::prefix('reception')->middleware('role:admin,reception')->group(function () {
        Route::post('/patients/upsert', [ReceptionController::class, 'upsertPatient']);
        Route::get('/patients/by-file/{fileNumber}', [ReceptionController::class, 'getPatientByFile']);
        Route::get('/doctors', [ReceptionController::class, 'getDoctorsByClinic']);
        Route::post('/visits', [ReceptionController::class, 'createVisit']);
        Route::post('/visits/calc-preview', [ReceptionController::class, 'calcPreview']);
    });

    // Clinics
    Route::prefix('clinics')->group(function () {
        Route::get('/statuses', [ClinicController::class, 'statuses']);
        Route::get('/active', [ClinicController::class, 'listActive']);
    });
    Route::apiResource('clinics', ClinicController::class);
    Route::post('clinics/{clinic}/archive', [ClinicController::class, 'archive']);
    Route::post('clinics/{clinic}/activate', [ClinicController::class, 'activate']);
    Route::post('clinics/{clinic}/deactivate', [ClinicController::class, 'deactivate']);
    Route::get('clinics/{clinic}/statistics', [ClinicController::class, 'statistics']);
    Route::get('clinics/{clinic}/detailed-report', [ClinicController::class, 'detailedReport']);
    Route::get('clinics/{clinic}/doctors', [ClinicController::class, 'doctors']);
    Route::post('clinics/{clinic}/assign-doctor', [ClinicController::class, 'assignDoctor']);
    Route::delete('clinics/{clinic}/doctors/{doctor}', [ClinicController::class, 'unassignDoctor']);

    // Doctors
    Route::prefix('doctors')->group(function () {
        Route::get('/active', [DoctorController::class, 'listActive']);
        Route::get('/specializations', [DoctorController::class, 'specializations']);
        Route::get('/{doctor}/statistics', [DoctorController::class, 'statistics']);
        Route::get('/{doctor}/finance', [DoctorController::class, 'finance']);
        Route::get('/{doctor}/schedule', [DoctorController::class, 'schedule']);
        Route::get('/{doctor}/appointments', [DoctorController::class, 'schedule']);
        Route::get('/{doctor}/patients', [DoctorController::class, 'patients']);
        Route::post('/{doctor}/archive', [DoctorController::class, 'archive']);
        Route::post('/{doctor}/activate', [DoctorController::class, 'activate']);
        Route::post('/{doctor}/deactivate', [DoctorController::class, 'deactivate']);
        Route::get('/{doctor}/deductions', [DoctorDeductionController::class, 'index']);
        Route::post('/{doctor}/deductions', [DoctorDeductionController::class, 'store']);
        Route::delete('/{doctor}/deductions/{deduction}', [DoctorDeductionController::class, 'destroy']);
    });
    Route::apiResource('doctors', DoctorController::class);

    // Users
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);

    // Patients
    Route::get('/patients/next-file-number', [PatientController::class, 'nextFileNumber']);
    Route::get('/patients/{patient}/visits', [PatientController::class, 'visits']);
    Route::apiResource('patients', PatientController::class);

    // Visits
    Route::apiResource('visits', VisitController::class);

    // Appointments
    Route::prefix('appointments')->group(function () {
        Route::get('statuses', [AppointmentController::class, 'statuses']);
        Route::get('calendar', [AppointmentController::class, 'calendar']);
        Route::get('check-availability', [AppointmentController::class, 'checkAvailability']);
    });
    Route::apiResource('appointments', AppointmentController::class);
    Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('appointments/{appointment}/mark-completed', [AppointmentController::class, 'markCompleted']);
    Route::post('appointments/{appointment}/mark-missed', [AppointmentController::class, 'markMissed']);
    Route::post('appointments/{appointment}/convert-to-visit', [AppointmentController::class, 'convertToVisit']);

    // Finance
    Route::prefix('finance')->middleware('role:admin')->group(function () {
        Route::get('/summary', [FinanceController::class, 'summary']);
        Route::get('/doctors', [FinanceController::class, 'doctors']);
        Route::get('/doctor/{doctorId}/details', [FinanceController::class, 'doctorDetails']);
        Route::post('/deductions', [FinanceController::class, 'storeDeduction']);
    });

    // Reports
    Route::prefix('reports')->middleware('role:admin')->group(function () {
        Route::get('/patients', [ReportController::class, 'patients']);
        Route::get('/visits', [ReportController::class, 'visits']);
        Route::get('/finance', [ReportController::class, 'finance']);
        Route::get('/daily', [ReportController::class, 'daily']);
        Route::get('/monthly', [ReportController::class, 'monthly']);
    });

    // Settings
    Route::prefix('settings')->middleware('role:admin')->group(function () {
        Route::get('/', [SettingsController::class, 'index']);
        Route::put('/', [SettingsController::class, 'update']);
    });
});
