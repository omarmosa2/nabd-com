<?php

namespace App\Models;

use App\Enums\DoctorPercentageType;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'role',
        'clinic_id',
        'phone',
        'specialization',
        'examination_fee',
        'percentage_type',
        'percentage_value',
        'is_active',
        'archived_at',
        'notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'percentage_type' => DoctorPercentageType::class,
            'examination_fee' => 'decimal:2',
            'percentage_value' => 'decimal:2',
            'is_active' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'doctor_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(DoctorDeduction::class, 'doctor_id');
    }

    public function patients()
    {
        return $this->hasManyThrough(
            Patient::class,
            Visit::class,
            'doctor_id',
            'id',
            'id',
            'patient_id'
        )->distinct();
    }

    public function procedures()
    {
        return $this->hasManyThrough(
            Procedure::class,
            Visit::class,
            'doctor_id',
            'visit_id',
            'id',
            'id'
        );
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isDoctor(): bool
    {
        return $this->role === UserRole::Doctor;
    }

    public function isReception(): bool
    {
        return $this->role === UserRole::Reception;
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function acceptsAppointments(): bool
    {
        return $this->isDoctor()
            && !$this->isArchived()
            && $this->is_active
            && ($this->clinic?->acceptsAppointments() ?? false);
    }

    public function acceptsVisits(): bool
    {
        return $this->isDoctor()
            && !$this->isArchived()
            && $this->is_active
            && ($this->clinic?->acceptsVisits() ?? false);
    }

    public function getPatientsCountAttribute(): int
    {
        return $this->visits()
            ->distinct('patient_id')
            ->count('patient_id');
    }

    public function getVisitsCountAttribute(): int
    {
        return $this->visits()->count();
    }

    public function getExaminationsCountAttribute(): int
    {
        return $this->visits()->where('visit_type', 'examination')->count();
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->visits()->where('visit_type', 'review')->count();
    }

    public function getFreeReviewsCountAttribute(): int
    {
        return $this->visits()->where('visit_type', 'review')->where('is_free_review', true)->count();
    }

    public function getProceduresCountAttribute(): int
    {
        return (float) Procedure::whereIn('visit_id', $this->visits()->select('id'))->count();
    }

    public function getExaminationRevenueAttribute(): float
    {
        return (float) $this->visits()
            ->where('visit_type', 'examination')
            ->sum('examination_fee');
    }

    public function getProcedureRevenueAttribute(): float
    {
        return (float) Procedure::whereIn('visit_id', $this->visits()->select('id'))->sum('doctor_fee');
    }

    public function getDeductionsTotalAttribute(): float
    {
        return (float) $this->deductions()
            ->whereIn('type', ['deduction', 'advance'])
            ->sum('amount');
    }

    public function getBonusesTotalAttribute(): float
    {
        return (float) $this->deductions()
            ->where('type', 'bonus')
            ->sum('amount');
    }

    public function getMonthlyRevenueAttribute(): float
    {
        return (float) $this->visits()
            ->whereYear('visit_date', now()->year)
            ->whereMonth('visit_date', now()->month)
            ->sum('examination_fee');
    }

    public function getTodayRevenueAttribute(): float
    {
        return (float) $this->visits()
            ->whereDate('visit_date', now()->toDateString())
            ->sum('examination_fee');
    }

    public function getYearlyRevenueAttribute(): float
    {
        return (float) $this->visits()
            ->whereYear('visit_date', now()->year)
            ->sum('examination_fee');
    }

    public function getNetEarningsAttribute(): float
    {
        $exams = $this->visits()
            ->where('visit_type', 'examination')
            ->sum('examination_fee');
        $procs = Procedure::whereIn('visit_id', $this->visits()->select('id'))->sum('doctor_fee');
        $gross = (float) $exams + (float) $procs;

        $deductions = (float) $this->deductions()
            ->whereIn('type', ['deduction', 'advance', 'other'])
            ->sum('amount');
        $bonuses = (float) $this->deductions()->where('type', 'bonus')->sum('amount');

        return $gross - $deductions + $bonuses;
    }

    public function scopeDoctors(Builder $q): Builder
    {
        return $q->where('role', UserRole::Doctor->value);
    }

    public function scopeActiveDoctors(Builder $q): Builder
    {
        return $q->where('role', UserRole::Doctor->value)
            ->where('is_active', true)
            ->whereNull('archived_at');
    }

    public function scopeNotArchived(Builder $q): Builder
    {
        return $q->whereNull('archived_at');
    }

    public function scopeInClinic(Builder $q, ?int $clinicId): Builder
    {
        return $clinicId ? $q->where('clinic_id', $clinicId) : $q;
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term) return $q;
        $like = '%' . $term . '%';
        return $q->where(function ($w) use ($like) {
            $w->where('full_name', 'like', $like)
              ->orWhere('email', 'like', $like)
              ->orWhere('phone', 'like', $like)
              ->orWhere('specialization', 'like', $like);
        });
    }

    public function scopeWithSpecialization(Builder $q, ?string $spec): Builder
    {
        return $spec ? $q->where('specialization', $spec) : $q;
    }

    public function scopeActiveFlag(Builder $q, $active): Builder
    {
        if ($active === null || $active === '' || $active === 'all') return $q;
        if ($active === '1' || $active === true || $active === 'active') {
            return $q->where('is_active', true);
        }
        if ($active === '0' || $active === false || $active === 'inactive') {
            return $q->where('is_active', false);
        }
        return $q;
    }
}
