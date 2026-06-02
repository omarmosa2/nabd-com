<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'clinic_id',
        'appointment_date',
        'duration_minutes',
        'status',
        'notes',
        'visit_id',
        'cancelled_at',
        'completed_at',
        'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'datetime',
            'duration_minutes' => 'integer',
            'status' => AppointmentStatus::class,
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function getEndTimeAttribute(): Carbon
    {
        return $this->appointment_date->copy()->addMinutes($this->duration_minutes ?? 30);
    }

    public function isPast(): bool
    {
        return $this->end_time_attribute?->isPast()
            ?? $this->appointment_date->copy()->addMinutes($this->duration_minutes ?? 30)->isPast();
    }

    public function isUpcoming(): bool
    {
        return $this->appointment_date->isFuture();
    }

    public function canBeConverted(): bool
    {
        return $this->status === AppointmentStatus::Scheduled
            || $this->status === AppointmentStatus::Completed;
    }

    public function canBeCancelled(): bool
    {
        return $this->status === AppointmentStatus::Scheduled;
    }

    public function scopeForDoctor(Builder $q, int $doctorId): Builder
    {
        return $q->where('doctor_id', $doctorId);
    }

    public function scopeOnDate(Builder $q, string $date): Builder
    {
        return $q->whereDate('appointment_date', $date);
    }

    public function scopeBetweenDates(Builder $q, string $from, string $to): Builder
    {
        return $q->whereBetween('appointment_date', [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay(),
        ]);
    }

    public function scopeInMonth(Builder $q, int $year, int $month): Builder
    {
        return $q->whereYear('appointment_date', $year)
            ->whereMonth('appointment_date', $month);
    }

    public function scopeUpcoming(Builder $q): Builder
    {
        return $q->where('appointment_date', '>=', now())
            ->where('status', AppointmentStatus::Scheduled->value);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereIn('status', [
            AppointmentStatus::Scheduled->value,
            AppointmentStatus::Completed->value,
        ]);
    }

    public function scopeWithStatus(Builder $q, AppointmentStatus|string $status): Builder
    {
        $value = $status instanceof AppointmentStatus ? $status->value : $status;
        return $q->where('status', $value);
    }
}
