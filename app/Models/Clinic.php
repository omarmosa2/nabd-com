<?php

namespace App\Models;

use App\Enums\ClinicStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    protected $fillable = [
        'name',
        'description',
        'location',
        'phone',
        'status',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ClinicStatus::class,
            'archived_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'doctor');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function patients()
    {
        return $this->hasManyThrough(
            Patient::class,
            Visit::class,
            'clinic_id',
            'id',
            'id',
            'patient_id'
        )->distinct();
    }

    public function isActive(): bool
    {
        return $this->status === ClinicStatus::Active;
    }

    public function isArchived(): bool
    {
        return $this->status === ClinicStatus::Archived;
    }

    public function acceptsAppointments(): bool
    {
        return $this->status?->acceptsAppointments() ?? false;
    }

    public function acceptsVisits(): bool
    {
        return $this->status?->acceptsVisits() ?? false;
    }

    public function hasActiveDoctors(): bool
    {
        return $this->doctors()->exists();
    }

    public function hasVisits(): bool
    {
        return $this->visits()->exists();
    }

    public function getDoctorsCountAttribute(): int
    {
        return $this->doctors()->count();
    }

    public function getVisitsCountAttribute(): int
    {
        return $this->visits()->count();
    }

    public function getAppointmentsCountAttribute(): int
    {
        return $this->appointments()->count();
    }

    public function getPatientsCountAttribute(): int
    {
        return $this->visits()->distinct('patient_id')->count('patient_id');
    }

    public function getMonthlyRevenueAttribute(): float
    {
        $visits = $this->visits()
            ->where('visit_date', '>=', now()->startOfMonth())
            ->with('procedures')
            ->get();
        $total = 0;
        foreach ($visits as $visit) {
            $total += (float) $visit->amount_received;
        }
        return round($total, 2);
    }

    public function getTodayRevenueAttribute(): float
    {
        return (float) $this->visits()
            ->whereDate('visit_date', today())
            ->sum('amount_received');
    }

    public function getYearlyRevenueAttribute(): float
    {
        return (float) $this->visits()
            ->where('visit_date', '>=', now()->startOfYear())
            ->sum('amount_received');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', ClinicStatus::Active->value);
    }

    public function scopeNotArchived(Builder $q): Builder
    {
        return $q->where('status', '!=', ClinicStatus::Archived->value);
    }

    public function scopeWithStatus(Builder $q, ClinicStatus|string $status): Builder
    {
        $value = $status instanceof ClinicStatus ? $status->value : $status;
        return $q->where('status', $value);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term) return $q;
        return $q->where(function (Builder $sub) use ($term) {
            $sub->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }
}
