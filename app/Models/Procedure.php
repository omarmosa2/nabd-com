<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Procedure extends Model
{
    protected $fillable = [
        'visit_id',
        'name',
        'center_fee',
        'doctor_fee',
    ];

    protected function casts(): array
    {
        return [
            'center_fee' => 'decimal:2',
            'doctor_fee' => 'decimal:2',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }
}
