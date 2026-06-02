<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class PatientService
{
    public function getPatients(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Patient::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('file_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['clinic_id'])) {
            $query->whereHas('visits', function($q) use ($filters) {
                $q->where('clinic_id', $filters['clinic_id']);
            });
        }

        if (!empty($filters['doctor_id'])) {
            $query->whereHas('visits', function($q) use ($filters) {
                $q->where('doctor_id', $filters['doctor_id']);
            });
        }

        if (!empty($filters['date'])) {
            $query->whereHas('visits', function($q) use ($filters) {
                $q->whereDate('visit_date', $filters['date']);
            });
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->with(['visits.doctor', 'visits.clinic'])
            ->withCount('visits')
            ->with(['visits' => function($q) {
                $q->latest('visit_date')->limit(1);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getPatientDetails(Patient $patient): array
    {
        $patient->load([
            'visits.doctor',
            'visits.clinic',
            'visits.procedures'
        ]);

        $visits = $patient->visits->map(function($visit) {
            $totals = app(VisitService::class)->computeVisitTotals($visit);
            return [
                'id' => $visit->id,
                'visit_date' => $visit->visit_date,
                'visit_type' => $visit->visit_type,
                'doctor' => [
                    'id' => $visit->doctor->id,
                    'full_name' => $visit->doctor->full_name,
                ],
                'clinic' => [
                    'id' => $visit->clinic->id,
                    'name' => $visit->clinic->name,
                ],
                'procedures' => $visit->procedures,
                'totals' => $totals,
            ];
        });

        return [
            'patient' => $patient,
            'visits' => $visits,
            'total_visits' => $patient->visits->count(),
            'total_spent' => $visits->sum('totals.total_fees'),
        ];
    }

    public function getPatientVisits(Patient $patient): array
    {
        $patient->load([
            'visits.doctor',
            'visits.clinic',
            'visits.procedures'
        ]);

        return $patient->visits->map(function($visit) {
            $totals = app(VisitService::class)->computeVisitTotals($visit);
            return [
                'id' => $visit->id,
                'visit_date' => $visit->visit_date->format('Y-m-d'),
                'visit_type' => $visit->visit_type,
                'is_free_review' => $visit->is_free_review,
                'doctor' => [
                    'id' => $visit->doctor->id,
                    'full_name' => $visit->doctor->full_name,
                ],
                'clinic' => [
                    'id' => $visit->clinic->id,
                    'name' => $visit->clinic->name,
                ],
                'procedures' => $visit->procedures->map(function($proc) {
                    return [
                        'id' => $proc->id,
                        'name' => $proc->name,
                        'center_fee' => $proc->center_fee,
                        'doctor_fee' => $proc->doctor_fee,
                    ];
                }),
                'totals' => $totals,
                'notes' => $visit->notes,
            ];
        })->sortByDesc('visit_date')->values()->toArray();
    }

    public function generateFileNumber(): string
    {
        $today = Carbon::now();
        $prefix = $today->format('Y-md');
        
        $lastPatient = Patient::whereDate('created_at', $today)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = 1;
        if ($lastPatient && preg_match('/-(\d{4})$/', $lastPatient->file_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        }
        
        return sprintf('%s-%04d', $prefix, $nextNumber);
    }
}
