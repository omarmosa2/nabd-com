<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\StoreVisitRequest;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use App\Services\VisitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceptionController extends Controller
{
    protected VisitService $visitService;

    public function __construct(VisitService $visitService)
    {
        $this->visitService = $visitService;
    }

    public function upsertPatient(StorePatientRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (!empty($data['file_number'])) {
            $patient = Patient::where('file_number', $data['file_number'])->first();
            if ($patient) {
                $patient->update($data);
                return response()->json([
                    'message' => 'Patient updated successfully',
                    'patient' => $patient
                ]);
            }
        }

        $data['file_number'] = $this->visitService->generateFileNumber();
        $patient = Patient::create($data);

        return response()->json([
            'message' => 'Patient created successfully',
            'patient' => $patient
        ], 201);
    }

    public function getPatientByFile(string $fileNumber): JsonResponse
    {
        $patient = Patient::where('file_number', $fileNumber)->first();

        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        return response()->json(['patient' => $patient]);
    }

    public function getDoctorsByClinic(Request $request): JsonResponse
    {
        $request->validate(['clinic_id' => 'required|exists:clinics,id']);

        $doctors = User::where('role', 'doctor')
            ->where('clinic_id', $request->clinic_id)
            ->get(['id', 'full_name', 'examination_fee']);

        return response()->json(['doctors' => $doctors]);
    }

    public function createVisit(StoreVisitRequest $request): JsonResponse
    {
        $data = $request->validated();

        $patient = Patient::findOrFail($data['patient_id']);
        $doctor = User::findOrFail($data['doctor_id']);

        $isFreeReview = false;
        if ($data['visit_type'] === 'review') {
            $isFreeReview = $this->visitService->isFreeReview($patient, $doctor);
        }

        $visit = Visit::create([
            'patient_id' => $data['patient_id'],
            'doctor_id' => $data['doctor_id'],
            'clinic_id' => $data['clinic_id'],
            'visit_date' => $data['visit_date'],
            'visit_type' => $data['visit_type'],
            'is_free_review' => $isFreeReview,
            'examination_fee' => $data['examination_fee'],
            'amount_received' => $data['amount_received'],
            'complex_discount' => $data['complex_discount'] ?? 0,
            'doctor_discount' => $data['doctor_discount'] ?? 0,
            'notes' => $data['notes'] ?? null,
        ]);

        if (!empty($data['procedures'])) {
            foreach ($data['procedures'] as $procedure) {
                $visit->procedures()->create($procedure);
            }
        }

        $totals = $this->visitService->computeVisitTotals($visit);

        event(new \App\Events\VisitCreated($visit));

        return response()->json([
            'message' => 'Visit created successfully',
            'visit' => $visit->load('procedures'),
            'totals' => $totals
        ], 201);
    }

    public function calcPreview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'visit_type' => 'required|in:examination,review',
            'examination_fee' => 'required|numeric|min:0',
            'complex_discount' => 'nullable|numeric|min:0',
            'doctor_discount' => 'nullable|numeric|min:0',
            'procedures' => 'nullable|array',
            'procedures.*.name' => 'required|string',
            'procedures.*.center_fee' => 'required|numeric|min:0',
            'procedures.*.doctor_fee' => 'required|numeric|min:0',
        ]);

        $patient = Patient::findOrFail($data['patient_id']);
        $doctor = User::findOrFail($data['doctor_id']);

        $isFreeReview = false;
        if ($data['visit_type'] === 'review') {
            $isFreeReview = $this->visitService->isFreeReview($patient, $doctor);
        }

        $tempVisit = new Visit([
            'examination_fee' => $data['examination_fee'],
            'complex_discount' => $data['complex_discount'] ?? 0,
            'doctor_discount' => $data['doctor_discount'] ?? 0,
        ]);

        if (!empty($data['procedures'])) {
            foreach ($data['procedures'] as $proc) {
                $tempVisit->procedures->push(new \App\Models\Procedure($proc));
            }
        }

        $totals = $this->visitService->computeVisitTotals($tempVisit);

        return response()->json([
            'is_free_review' => $isFreeReview,
            'totals' => $totals
        ]);
    }
}
