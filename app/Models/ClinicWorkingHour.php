<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicWorkingHour extends Model
{
    public const DAYS = [
        'saturday',
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
    ];

    public const DAY_LABELS = [
        'saturday' => 'السبت',
        'sunday' => 'الأحد',
        'monday' => 'الإثنين',
        'tuesday' => 'الثلاثاء',
        'wednesday' => 'الأربعاء',
        'thursday' => 'الخميس',
        'friday' => 'الجمعة',
    ];

    protected $fillable = [
        'clinic_id',
        'day_of_week',
        'is_active',
        'start_time',
        'end_time',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function toScheduleArray(): array
    {
        return [
            'day_of_week' => $this->day_of_week,
            'day_label' => self::DAY_LABELS[$this->day_of_week] ?? $this->day_of_week,
            'is_active' => (bool) $this->is_active,
            'start_time' => $this->is_active ? self::formatTime($this->start_time) : null,
            'end_time' => $this->is_active ? self::formatTime($this->end_time) : null,
        ];
    }

    public static function emptySchedule(): array
    {
        return array_map(fn (string $day) => [
            'day_of_week' => $day,
            'day_label' => self::DAY_LABELS[$day],
            'is_active' => false,
            'start_time' => null,
            'end_time' => null,
        ], self::DAYS);
    }

    public static function formatTime(?string $time): ?string
    {
        return $time ? substr($time, 0, 5) : null;
    }
}
