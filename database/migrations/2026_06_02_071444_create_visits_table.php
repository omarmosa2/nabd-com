<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->date('visit_date');
            $table->string('visit_type');
            $table->boolean('is_free_review')->default(false);
            $table->decimal('examination_fee', 10, 2)->default(0);
            $table->decimal('amount_received', 10, 2)->default(0);
            $table->decimal('complex_discount', 10, 2)->default(0);
            $table->decimal('doctor_discount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
