<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('day_of_week', 16);
            $table->boolean('is_active')->default(false);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();

            $table->unique(['clinic_id', 'day_of_week'], 'clinic_working_hours_clinic_day_unique');
            $table->index(['day_of_week', 'is_active'], 'clinic_working_hours_day_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_working_hours');
    }
};
