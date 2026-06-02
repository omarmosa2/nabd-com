<?php

namespace App\Http\Controllers;

use App\Enums\ClinicStatus;
use App\Exceptions\ClinicOperationException;
use App\Http\Requests\StoreClinicRequest;
use App\Http\Requests\UpdateClinicRequest;
use App\Http\Resources\ClinicResource;
use App\Models\Clinic;
use App\Models\User;
use App\Services\ClinicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClinicController extends Controller
{
    public function __construct(protected ClinicService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $clinics = $this->service->paginate(
            filters: $request->only(['search', 'status', 'min_doctors', 'active_only']),
            perPage: (int) $request->input('per_page', 20),
            viewer: $request->user(),
        );
        return ClinicResource::collection($clinics);
    }

    public function listActive(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->listActive(),
        ]);
    }

    public function store(StoreClinicRequest $request): JsonResponse
    {
        $clinic = $this->service->createClinic($request->validated(), $request->user());
        return (new ClinicResource($clinic))->response()->setStatusCode(201);
    }

    public function show(Clinic $clinic): ClinicResource
    {
        $this->authorizeView($clinic);
        $clinic->load('workingHours');
        $clinic->loadCount(['users', 'visits', 'appointments']);
        return new ClinicResource($clinic);
    }

    public function update(UpdateClinicRequest $request, Clinic $clinic): ClinicResource
    {
        $updated = $this->service->updateClinic($clinic, $request->validated(), $request->user());
        $updated->loadCount(['users', 'visits', 'appointments']);
        return new ClinicResource($updated);
    }

    public function destroy(Clinic $clinic, Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'غير مصرح لك بحذف عيادة.'], 403);
        }
        try {
            $this->service->deleteClinic($clinic, $request->user());
        } catch (ClinicOperationException $e) {
            throw $e;
        }
        return response()->json(['message' => 'تم حذف العيادة بنجاح.']);
    }

    public function archive(Clinic $clinic, Request $request): ClinicResource
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'غير مصرح لك بأرشفة عيادة.'], 403);
        }
        $updated = $this->service->archiveClinic($clinic, $request->user());
        $updated->loadCount(['users', 'visits', 'appointments']);
        return new ClinicResource($updated);
    }

    public function activate(Clinic $clinic, Request $request): ClinicResource
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'غير مصرح.'], 403);
        }
        $updated = $this->service->activateClinic($clinic, $request->user());
        $updated->loadCount(['users', 'visits', 'appointments']);
        return new ClinicResource($updated);
    }

    public function deactivate(Clinic $clinic, Request $request): ClinicResource
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'غير مصرح.'], 403);
        }
        $updated = $this->service->deactivateClinic($clinic, $request->user());
        $updated->loadCount(['users', 'visits', 'appointments']);
        return new ClinicResource($updated);
    }

    public function statistics(Clinic $clinic, Request $request): JsonResponse
    {
        $this->authorizeView($clinic);
        return response()->json($this->service->getStatistics($clinic));
    }

    public function detailedReport(Clinic $clinic, Request $request): JsonResponse
    {
        $this->authorizeView($clinic);
        $limit = (int) $request->input('limit', 5);
        return response()->json($this->service->getDetailedReport($clinic, $limit));
    }

    public function doctors(Clinic $clinic, Request $request): JsonResponse
    {
        $this->authorizeView($clinic);
        $doctors = $clinic->users()
            ->where('role', 'doctor')
            ->withCount('visits')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'email', 'examination_fee'])
            ->map(fn ($d) => [
                'id' => $d->id,
                'full_name' => $d->full_name,
                'email' => $d->email,
                'examination_fee' => (float) $d->examination_fee,
                'visits_count' => $d->visits_count,
            ]);
        return response()->json(['data' => $doctors]);
    }

    public function assignDoctor(Clinic $clinic, Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'غير مصرح.'], 403);
        }
        $data = $request->validate([
            'doctor_id' => ['required', 'exists:users,id'],
        ]);
        $doctor = $this->service->assignDoctor($clinic, (int) $data['doctor_id'], $request->user());
        return response()->json([
            'message' => 'تم تعيين الطبيب للعيادة بنجاح.',
            'doctor' => [
                'id' => $doctor->id,
                'full_name' => $doctor->full_name,
            ],
        ]);
    }

    public function unassignDoctor(Clinic $clinic, int $doctor, Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'غير مصرح.'], 403);
        }
        $this->service->unassignDoctor($clinic, $doctor, $request->user());
        return response()->json(['message' => 'تم إزالة الطبيب من العيادة.']);
    }

    public function statuses(): JsonResponse
    {
        return response()->json([
            'data' => collect(ClinicStatus::cases())->map(fn (ClinicStatus $s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'color' => $s->color(),
                'accepts_appointments' => $s->acceptsAppointments(),
                'accepts_visits' => $s->acceptsVisits(),
            ])->values(),
        ]);
    }

    protected function authorizeView(Clinic $clinic): void
    {
        $user = request()->user();
        if ($user->isDoctor() && $user->clinic_id !== $clinic->id) {
            abort(403, 'لا يمكنك عرض عيادة أخرى.');
        }
    }
}
