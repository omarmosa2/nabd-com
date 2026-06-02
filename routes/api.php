<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DashboardController;
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
        Route::get('/top-doctors', [DashboardController::class, 'topDoctors']);
        Route::get('/top-clinics', [DashboardController::class, 'topClinics']);
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
    Route::get('/clinics', [ClinicController::class, 'index']);
    Route::get('/clinics/{clinic}', [ClinicController::class, 'show']);

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
    Route::apiResource('appointments', AppointmentController::class);

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
