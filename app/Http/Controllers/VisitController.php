<?php

namespace App\Http\Controllers;

use App\Enums\VisitType;
use App\Events\VisitCreated;
use App\Http\Requests\StoreVisitRequest;
use App\Http\Requests\UpdateVisitRequest;
use App\Http\Resources\VisitResource;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use App\Services\VisitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    public function __construct(
        protected VisitService $visitService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Visit::class);

        $query = Visit::with(['patient', 'doctor', 'clinic', 'procedures']);

        if ($request->user()->isDoctor()) {
            $query->where('doctor_id', $request->user()->id);
        }

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->has('clinic_id')) {
            $query->where('clinic_id', $request->clinic_id);
        }

        if ($request->has('date')) {
            $query->whereDate('visit_date', $request->date);
        }

        $visits = $query->latest('visit_date')->paginate($request->get('per_page', 15));

        return VisitResource::collection($visits);
    }

    public function store(StoreVisitRequest $request): JsonResponse
    {
        $data = $request->validated();

        $visit = DB::transaction(function () use ($data, $request) {
            $patient = Patient::findOrFail($data['patient_id']);
            $doctor = User::findOrFail($data['doctor_id']);

            $isFreeReview = false;
            if (($data['visit_type'] ?? null) === VisitType::Review->value) {
                $isFreeReview = $this->visitService->isFreeReview($patient, $doctor);
            }

            $data['is_free_review'] = $isFreeReview;
            $data['examination_fee'] = $data['examination_fee'] ?? $doctor->examination_fee;

            $proceduresData = $data['procedures'] ?? [];
            unset($data['procedures']);

            $visit = Visit::create($data);

            foreach ($proceduresData as $procedure) {
                $visit->procedures()->create($procedure);
            }

            return $visit;
        });

        $visit->load(['patient', 'doctor', 'clinic', 'procedures']);

        $totals = $this->visitService->computeVisitTotals($visit);

        VisitCreated::dispatch(
            $visit->id,
            $visit->patient->full_name,
            $visit->doctor->full_name,
            $totals['total_fees'],
        );

        return (new VisitResource($visit))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Visit $visit): VisitResource
    {
        $this->authorize('view', $visit);

        $visit->load(['patient', 'doctor', 'clinic', 'procedures']);

        return new VisitResource($visit);
    }

    public function update(UpdateVisitRequest $request, Visit $visit): VisitResource
    {
        $data = $request->validated();

        DB::transaction(function () use ($visit, $data) {
            $proceduresData = $data['procedures'] ?? null;
            unset($data['procedures']);

            $visit->update($data);

            if ($proceduresData !== null) {
                $visit->procedures()->delete();
                foreach ($proceduresData as $procedure) {
                    $visit->procedures()->create($procedure);
                }
            }
        });

        $visit->load(['patient', 'doctor', 'clinic', 'procedures']);

        return new VisitResource($visit);
    }

    public function destroy(Visit $visit): JsonResponse
    {
        $this->authorize('delete', $visit);

        $visit->delete();

        return response()->json(['message' => 'Visit deleted successfully']);
    }

    public function calcPreview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'visit_type' => 'required|in:examination,review',
            'examination_fee' => 'nullable|numeric|min:0',
            'complex_discount' => 'nullable|numeric|min:0',
            'doctor_discount' => 'nullable|numeric|min:0',
            'procedures' => 'nullable|array',
            'procedures.*.name' => 'required_with:procedures|string',
            'procedures.*.center_fee' => 'required_with:procedures|numeric|min:0',
            'procedures.*.doctor_fee' => 'required_with:procedures|numeric|min:0',
        ]);

        $patient = Patient::findOrFail($data['patient_id']);
        $doctor = User::findOrFail($data['doctor_id']);

        $isFreeReview = false;
        if ($data['visit_type'] === VisitType::Review->value) {
            $isFreeReview = $this->visitService->isFreeReview($patient, $doctor);
        }

        $examinationFee = $data['examination_fee'] ?? $doctor->examination_fee;
        $complexDiscount = $data['complex_discount'] ?? 0;
        $doctorDiscount = $data['doctor_discount'] ?? 0;

        $procedures = $data['procedures'] ?? [];
        $doctorProcedures = collect($procedures)->sum('doctor_fee');
        $centerProcedures = collect($procedures)->sum('center_fee');

        $doctorExamShare = $examinationFee - $doctorDiscount - $complexDiscount;
        $centerExam = $complexDiscount;

        $totalFees = $examinationFee + $doctorProcedures + $centerProcedures;
        $doctorShare = $doctorExamShare + $doctorProcedures;
        $centerShare = $centerExam + $centerProcedures;

        return response()->json([
            'is_free_review' => $isFreeReview,
            'examination_fee' => $examinationFee,
            'doctor_procedures' => $doctorProcedures,
            'center_procedures' => $centerProcedures,
            'total_fees' => round($totalFees, 2),
            'doctor_share' => round($doctorShare, 2),
            'center_share' => round($centerShare, 2),
        ]);
    }
}
