<?php

namespace App\Enums;

enum DoctorPercentageType: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'مبلغ ثابت',
            self::Percentage => 'نسبة مئوية',
        };
    }

    public static function values(): array
    {
        return array_map(fn($c) => $c->value, self::cases());
    }
}
