<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Exceptions\AppointmentConflictException;
use App\Http\Requests\CheckAvailabilityRequest;
use App\Http\Requests\ConvertAppointmentToVisitRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AppointmentController extends Controller
{
    public function __construct(protected AppointmentService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $appointments = $this->service->paginate(
            filters: $request->only([
                'doctor_id', 'clinic_id', 'status', 'date',
                'date_from', 'date_to', 'search',
            ]),
            perPage: (int) $request->input('per_page', 25),
            viewer: $request->user(),
        );

        return AppointmentResource::collection($appointments);
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        try {
            $appointment = $this->service->createAppointment(
                $request->validated(),
                $request->user(),
            );
        } catch (AppointmentConflictException $e) {
            throw $e;
        }

        return (new AppointmentResource($appointment))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Appointment $appointment): AppointmentResource
    {
        $this->authorizeView($appointment);
        $appointment->load(['patient', 'doctor', 'clinic', 'visit']);
        return new AppointmentResource($appointment);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): AppointmentResource
    {
        $updated = $this->service->updateAppointment(
            $appointment,
            $request->validated(),
            $request->user(),
        );
        return new AppointmentResource($updated);
    }

    public function destroy(Appointment $appointment, Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin() && !$request->user()->isReception()) {
            return response()->json(['message' => 'غير مصرح لك بحذف موعد.'], 403);
        }
        $this->service->deleteAppointment($appointment, $request->user());
        return response()->json(['message' => 'تم حذف الموعد بنجاح.']);
    }

    public function calendar(Request $request): JsonResponse
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $days = $this->service->calendar(
            year: $year,
            month: $month,
            filters: $request->only(['doctor_id', 'clinic_id']),
            viewer: $request->user(),
        );

        return response()->json([
            'year' => $year,
            'month' => $month,
            'days' => $days,
        ]);
    }

    public function checkAvailability(CheckAvailabilityRequest $request): JsonResponse
    {
        $result = $this->service->checkAvailability(
            doctorId: (int) $request->input('doctor_id'),
            date: Carbon::parse($request->input('appointment_date')),
            duration: $request->duration(),
            ignoreId: $request->filled('ignore_id') ? (int) $request->input('ignore_id') : null,
        );
        return response()->json($result);
    }

    public function cancel(Request $request, Appointment $appointment): AppointmentResource
    {
        if (!$request->user()->isAdmin() && !$request->user()->isReception()) {
            return abort(403, 'غير مصرح لك بإلغاء المواعيد.');
        }
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $updated = $this->service->cancelAppointment(
            $appointment,
            $request->input('reason'),
            $request->user(),
        );
        return new AppointmentResource($updated);
    }

    public function markCompleted(Appointment $appointment, Request $request): AppointmentResource
    {
        if (!$request->user()->isAdmin() && !$request->user()->isReception()) {
            return abort(403, 'غير مصرح لك.');
        }
        $updated = $this->service->markCompleted($appointment, $request->user());
        return new AppointmentResource($updated);
    }

    public function markMissed(Appointment $appointment, Request $request): AppointmentResource
    {
        if (!$request->user()->isAdmin() && !$request->user()->isReception()) {
            return abort(403, 'غير مصرح لك.');
        }
        $updated = $this->service->markMissed($appointment, $request->user());
        return new AppointmentResource($updated);
    }

    public function convertToVisit(
        ConvertAppointmentToVisitRequest $request,
        Appointment $appointment,
    ): JsonResponse {
        $visit = $this->service->convertToVisit(
            $appointment,
            $request->validated(),
            $request->user(),
        );

        $appointment->refresh()->load(['patient', 'doctor', 'clinic', 'visit']);

        return response()->json([
            'message' => 'تم تحويل الموعد إلى زيارة بنجاح.',
            'appointment' => new AppointmentResource($appointment),
            'visit' => $visit->load('procedures'),
        ], 201);
    }

    public function statuses(): JsonResponse
    {
        return response()->json([
            'data' => collect(AppointmentStatus::cases())->map(fn (AppointmentStatus $s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'color' => $s->color(),
                'is_terminal' => $s->isTerminal(),
            ])->values(),
        ]);
    }

    protected function authorizeView(Appointment $appointment): void
    {
        $user = request()->user();
        if ($user->isDoctor() && $appointment->doctor_id !== $user->id) {
            abort(403, 'لا يمكنك عرض مواعيد طبيب آخر.');
        }
    }
}
