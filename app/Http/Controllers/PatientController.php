<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    protected PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Patient::class);

        $filters = $request->only(['search', 'clinic_id', 'doctor_id', 'date', 'gender', 'date_from', 'date_to']);
        $patients = $this->patientService->getPatients($filters, $request->get('per_page', 15));

        return response()->json($patients);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Patient::class);

        $data = $request->validate([
            'file_number' => 'nullable|string|unique:patients,file_number',
            'full_name' => 'required|string|max:255',
            'age' => 'required|integer|min:0|max:150',
            'gender' => 'required|in:male,female',
            'residence' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        if (empty($data['file_number'])) {
            $data['file_number'] = $this->patientService->generateFileNumber();
        }

        $patient = Patient::create($data);

        return response()->json([
            'message' => 'Patient created successfully',
            'patient' => $patient
        ], 201);
    }

    public function stats()
    {
        $total_patients = Patient::count();
        $male_patients = Patient::where('gender', 'male')->count();
        $female_patients = Patient::where('gender', 'female')->count();
        $total_visits = \App\Models\Visit::count();
        $total_revenue = \App\Models\Visit::sum('amount_received') ?? 0;

        return response()->json([
            'total_patients' => $total_patients,
            'male_patients' => $male_patients,
            'female_patients' => $female_patients,
            'total_visits' => $total_visits,
            'total_revenue' => $total_revenue
        ]);
    }

    public function visits(Patient $patient)
    {
        $this->authorize('view', $patient);

        $visits = $this->patientService->getPatientVisits($patient);

        return response()->json(['visits' => $visits]);
    }

    public function show(Patient $patient)
    {
        $this->authorize('view', $patient);

        $details = $this->patientService->getPatientDetails($patient);

        return response()->json($details);
    }

    public function update(Request $request, Patient $patient)
    {
        $this->authorize('update', $patient);

        $data = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'age' => 'sometimes|integer|min:0',
            'gender' => 'sometimes|in:male,female',
            'residence' => 'nullable|string|max:255',
            'phone' => 'sometimes|string|max:20',
        ]);

        $patient->update($data);

        return response()->json([
            'message' => 'Patient updated successfully',
            'patient' => $patient
        ]);
    }

    public function destroy(Patient $patient)
    {
        $this->authorize('delete', $patient);

        $patient->delete();

        return response()->json(['message' => 'تم حذف المريض بنجاح']);
    }
}
