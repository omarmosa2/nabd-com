<?php

namespace App\Http\Controllers;

use App\Events\AppointmentCreated;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AppointmentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Appointment::with(['patient', 'doctor', 'clinic']);

        if ($request->user()->isDoctor()) {
            $query->where('doctor_id', $request->user()->id);
        }

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->has('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        $appointments = $query->orderBy('appointment_date')->paginate($request->get('per_page', 15));

        return AppointmentResource::collection($appointments);
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $appointment = Appointment::create($request->validated());
        $appointment->load(['patient', 'doctor', 'clinic']);

        AppointmentCreated::dispatch(
            $appointment->id,
            $appointment->patient->full_name,
            $appointment->doctor->full_name,
            $appointment->appointment_date->toDateTimeString(),
        );

        return (new AppointmentResource($appointment))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Appointment $appointment): AppointmentResource
    {
        $appointment->load(['patient', 'doctor', 'clinic']);

        return new AppointmentResource($appointment);
    }

    public function destroy(Appointment $appointment): JsonResponse
    {
        $appointment->delete();

        return response()->json(['message' => 'Appointment deleted successfully']);
    }

    public function update(Request $request, Appointment $appointment): AppointmentResource
    {
        $data = $request->validate([
            'patient_id' => 'sometimes|exists:patients,id',
            'doctor_id' => 'sometimes|exists:users,id',
            'clinic_id' => 'sometimes|exists:clinics,id',
            'appointment_date' => 'sometimes|date|after:now',
            'notes' => 'nullable|string',
        ]);

        $appointment->update($data);
        $appointment->load(['patient', 'doctor', 'clinic']);

        return new AppointmentResource($appointment);
    }
}
