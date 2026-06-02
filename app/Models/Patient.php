<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $fillable = [
        'file_number',
        'full_name',
        'age',
        'gender',
        'residence',
        'phone',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'gender' => Gender::class,
        ];
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
