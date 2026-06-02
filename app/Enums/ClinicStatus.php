<?php

namespace App\Enums;

enum ClinicStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'نشطة',
            self::Inactive => 'غير نشطة',
            self::Archived => 'مؤرشفة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => '#0f9f6e',
            self::Inactive => '#d97706',
            self::Archived => '#6b7280',
        };
    }

    public function acceptsAppointments(): bool
    {
        return $this === self::Active;
    }

    public function acceptsVisits(): bool
    {
        return $this === self::Active;
    }

    public function isDeletable(): bool
    {
        return $this === self::Archived;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
