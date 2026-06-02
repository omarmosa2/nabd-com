<?php

namespace App\Models;

use App\Enums\VisitType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'clinic_id',
        'visit_date',
        'visit_type',
        'is_free_review',
        'examination_fee',
        'amount_received',
        'complex_discount',
        'doctor_discount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'visit_type' => VisitType::class,
            'is_free_review' => 'boolean',
            'examination_fee' => 'decimal:2',
            'amount_received' => 'decimal:2',
            'complex_discount' => 'decimal:2',
            'doctor_discount' => 'decimal:2',
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

    public function procedures(): HasMany
    {
        return $this->hasMany(Procedure::class);
    }
}
