<?php

namespace App\Enums;

enum DeductionType: string
{
    case Deduction = 'deduction';
    case Advance = 'advance';
    case Bonus = 'bonus';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Deduction => 'خصم',
            self::Advance => 'سلفة',
            self::Bonus => 'مكافأة',
            self::Other => 'أخرى',
        };
    }

    public function affectsNet(): string
    {
        return match ($this) {
            self::Deduction, self::Advance => '-',
            self::Bonus => '+',
            self::Other => '-',
        };
    }

    public static function values(): array
    {
        return array_map(fn($c) => $c->value, self::cases());
    }
}
