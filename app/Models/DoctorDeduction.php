<?php

namespace App\Models;

use App\Enums\DeductionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorDeduction extends Model
{
    protected $fillable = [
        'doctor_id',
        'amount',
        'reason',
        'deduction_date',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'deduction_date' => 'date',
            'type' => DeductionType::class,
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function scopeOfType(Builder $q, DeductionType|string $type): Builder
    {
        $value = $type instanceof DeductionType ? $type->value : $type;
        return $q->where('type', $value);
    }

    public function scopeInMonth(Builder $q, int $year, int $month): Builder
    {
        return $q->whereYear('deduction_date', $year)
            ->whereMonth('deduction_date', $month);
    }
}
