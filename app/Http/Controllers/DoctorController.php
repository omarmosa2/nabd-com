<?php

namespace App\Http\Controllers;

use App\Exceptions\DoctorOperationException;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Models\User;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoctorController extends Controller
{
    public function __construct(protected DoctorService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $doctors = $this->service->paginate(
            filters: $request->only(['search', 'clinic_id', 'specialization', 'active', 'archived_only']),
            perPage: (int) $request->input('per_page', 20),
            viewer: $request->user(),
        );
        return DoctorResource::collection($doctors);
    }

    public function listActive(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->service->listActive(
                $request->input('clinic_id') ? (int) $request->input('clinic_id') : null
            ),
        ]);
    }

    public function specializations(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getSpecializations(),
        ]);
    }

    public function show(Request $request, User $doctor): DoctorResource
    {
        $this->assertDoctor($doctor);
        $this->authorizeView($request, $doctor);
        $doctor->load('clinic');
        $doctor->loadCount(['visits', 'appointments', 'deductions']);
        return new DoctorResource($doctor);
    }

    public function store(StoreDoctorRequest $request): JsonResponse
    {
        $doctor = $this->service->createDoctor($request->validated(), $request->user());
        $doctor->loadCount(['visits', 'appointments', 'deductions']);
        return (new DoctorResource($doctor))->response()->setStatusCode(201);
    }

    public function update(UpdateDoctorRequest $request, User $doctor): DoctorResource
    {
        $this->assertDoctor($doctor);
        $updated = $this->service->updateDoctor($doctor, $request->validated(), $request->user());
        $updated->loadCount(['visits', 'appointments', 'deductions']);
        return new DoctorResource($updated);
    }

    public function destroy(Request $request, User $doctor): JsonResponse
    {
        $this->assertDoctor($doctor);
        if (!$request->user()->isAdmin()) {
            throw new DoctorOperationException('غير مصرح لك بحذف طبيب.', [], [], 403);
        }
        $this->service->archiveDoctor($doctor, $request->user());
        return response()->json(['message' => 'تم أرشفة الطبيب بنجاح.']);
    }

    public function archive(Request $request, User $doctor): JsonResponse
    {
        $this->assertDoctor($doctor);
        if (!$request->user()->can('archive', $doctor)) {
            throw new DoctorOperationException('غير مصرح لك بأرشفة طبيب.', [], [], 403);
        }
        $this->service->archiveDoctor($doctor, $request->user());
        return response()->json(['message' => 'تم أرشفة الطبيب بنجاح.']);
    }

    public function activate(Request $request, User $doctor): JsonResponse
    {
        $this->assertDoctor($doctor);
        if (!$request->user()->can('activate', $doctor)) {
            throw new DoctorOperationException('غير مصرح لك بتفعيل طبيب.', [], [], 403);
        }
        $this->service->activateDoctor($doctor, $request->user());
        return response()->json(['message' => 'تم تفعيل الطبيب بنجاح.']);
    }

    public function deactivate(Request $request, User $doctor): JsonResponse
    {
        $this->assertDoctor($doctor);
        if (!$request->user()->can('deactivate', $doctor)) {
            throw new DoctorOperationException('غير مصرح لك بإلغاء تفعيل طبيب.', [], [], 403);
        }
        $this->service->deactivateDoctor($doctor, $request->user());
        return response()->json(['message' => 'تم إلغاء تفعيل الطبيب بنجاح.']);
    }

    public function statistics(Request $request, User $doctor): JsonResponse
    {
        $this->assertDoctor($doctor);
        $this->authorizeView($request, $doctor);
        return response()->json(['data' => $this->service->getStatistics($doctor)]);
    }

    public function finance(Request $request, User $doctor): JsonResponse
    {
        $this->assertDoctor($doctor);
        if (!$request->user()->can('viewFinance', $doctor)) {
            throw new DoctorOperationException('غير مصرح بعرض البيانات المالية.', [], [], 403);
        }
        return response()->json([
            'data' => $this->service->getFinance(
                $doctor,
                $request->input('from'),
                $request->input('to'),
            ),
        ]);
    }

    public function schedule(Request $request, User $doctor): JsonResponse
    {
        $this->assertDoctor($doctor);
        $this->authorizeView($request, $doctor);
        return response()->json([
            'data' => $this->service->getSchedule(
                $doctor,
                $request->input('range', 'week'),
                $request->input('date'),
            ),
        ]);
    }

    public function patients(Request $request, User $doctor): JsonResponse
    {
        $this->assertDoctor($doctor);
        $this->authorizeView($request, $doctor);
        return response()->json([
            'data' => $this->service->getPatients($doctor, (int) $request->input('limit', 20)),
        ]);
    }

    protected function authorizeView(Request $request, User $doctor): void
    {
        if (!$request->user()->can('view', $doctor)) {
            throw new DoctorOperationException('غير مصرح لك بعرض هذا الطبيب.', [], [], 403);
        }
    }

    protected function assertDoctor(User $doctor): void
    {
        if (!$doctor->isDoctor()) {
            throw new DoctorOperationException('المستخدم المحدد ليس طبيباً.', [], [], 404);
        }
    }
}
