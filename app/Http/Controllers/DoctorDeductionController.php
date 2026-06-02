<?php

namespace App\Http\Controllers;

use App\Exceptions\DoctorOperationException;
use App\Http\Requests\StoreDeductionRequest;
use App\Http\Resources\DoctorDeductionResource;
use App\Models\User;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorDeductionController extends Controller
{
    public function __construct(protected DoctorService $service) {}

    public function index(Request $request, User $doctor): JsonResponse
    {
        if (!$request->user()->can('viewFinance', $doctor)) {
            throw new DoctorOperationException('غير مصرح بعرض الخصومات.', [], [], 403);
        }
        $deductions = $doctor->deductions()
            ->orderByDesc('deduction_date')
            ->orderByDesc('id')
            ->get();
        return response()->json(['data' => DoctorDeductionResource::collection($deductions)->resolve()]);
    }

    public function store(StoreDeductionRequest $request, User $doctor): JsonResponse
    {
        $deduction = $this->service->addDeduction($doctor, $request->validated(), $request->user());
        return (new DoctorDeductionResource($deduction))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, User $doctor, int $deduction): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            throw new DoctorOperationException('غير مصرح بحذف الخصم.', [], [], 403);
        }
        $d = $doctor->deductions()->findOrFail($deduction);
        $d->delete();
        return response()->json(['message' => 'تم حذف الخصم بنجاح.']);
    }
}
