<?php

namespace App\Models;

use App\Enums\ClinicStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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

    public function workingHours(): HasMany
    {
        return $this->hasMany(ClinicWorkingHour::class);
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

    public function isOpenFor(Carbon $start, int $durationMinutes): bool
    {
        $workingHour = $this->workingHourFor($start);
        if (!$workingHour || !$workingHour->is_active || !$workingHour->start_time || !$workingHour->end_time) {
            return false;
        }

        $openAt = $start->copy()->setTimeFromTimeString((string) $workingHour->start_time);
        $closeAt = $start->copy()->setTimeFromTimeString((string) $workingHour->end_time);
        $end = $start->copy()->addMinutes($durationMinutes);

        return $start->greaterThanOrEqualTo($openAt) && $end->lessThanOrEqualTo($closeAt);
    }

    public function workingHourFor(Carbon $date): ?ClinicWorkingHour
    {
        $day = self::dayOfWeekKey($date);

        if ($this->relationLoaded('workingHours')) {
            return $this->workingHours->firstWhere('day_of_week', $day);
        }

        return $this->workingHours()->where('day_of_week', $day)->first();
    }

    public function workingHoursSchedule(): array
    {
        $hours = $this->relationLoaded('workingHours')
            ? $this->workingHours
            : $this->workingHours()->get();
        $byDay = $hours->keyBy('day_of_week');

        return collect(ClinicWorkingHour::DAYS)
            ->map(function (string $day) use ($byDay) {
                $hour = $byDay->get($day);
                if (!$hour) {
                    return [
                        'day_of_week' => $day,
                        'day_label' => ClinicWorkingHour::DAY_LABELS[$day],
                        'is_active' => false,
                        'start_time' => null,
                        'end_time' => null,
                    ];
                }

                return $hour->toScheduleArray();
            })
            ->values()
            ->all();
    }

    public static function dayOfWeekKey(Carbon $date): string
    {
        return match ($date->dayOfWeek) {
            Carbon::SATURDAY => 'saturday',
            Carbon::SUNDAY => 'sunday',
            Carbon::MONDAY => 'monday',
            Carbon::TUESDAY => 'tuesday',
            Carbon::WEDNESDAY => 'wednesday',
            Carbon::THURSDAY => 'thursday',
            Carbon::FRIDAY => 'friday',
        };
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
