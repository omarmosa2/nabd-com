<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Missed = 'missed';
    case ConvertedToVisit = 'converted_to_visit';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'مجدول',
            self::Completed => 'مكتمل',
            self::Cancelled => 'ملغي',
            self::Missed => 'فائت',
            self::ConvertedToVisit => 'تم التحويل لزيارة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Scheduled => '#2563eb',
            self::Completed => '#0f9f6e',
            self::Cancelled => '#dc2626',
            self::Missed => '#d97706',
            self::ConvertedToVisit => '#7c3aed',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Completed,
            self::Cancelled,
            self::Missed,
            self::ConvertedToVisit,
        ], true);
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
