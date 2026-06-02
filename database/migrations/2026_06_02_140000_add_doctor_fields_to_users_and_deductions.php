<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 32)->nullable()->unique()->after('email');
            $table->string('specialization')->nullable()->after('clinic_id');
            $table->string('percentage_type', 32)->default('fixed')->after('examination_fee');
            $table->decimal('percentage_value', 5, 2)->default(0)->after('percentage_type');
            $table->boolean('is_active')->default(true)->after('percentage_value');
            $table->timestamp('archived_at')->nullable()->after('is_active');
            $table->text('notes')->nullable()->after('archived_at');

            $table->index(['role', 'is_active']);
            $table->index('clinic_id');
            $table->index('archived_at');
        });

        Schema::table('doctor_deductions', function (Blueprint $table) {
            $table->date('deduction_date')->nullable()->after('reason');
            $table->string('type', 32)->default('deduction')->after('deduction_date');
            $table->index(['doctor_id', 'deduction_date']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_deductions', function (Blueprint $table) {
            $table->dropIndex(['doctor_id', 'deduction_date']);
            $table->dropIndex(['type']);
            $table->dropColumn(['deduction_date', 'type']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'is_active']);
            $table->dropIndex(['clinic_id']);
            $table->dropIndex(['archived_at']);
            $table->dropUnique(['phone']);
            $table->dropColumn([
                'phone', 'specialization', 'percentage_type', 'percentage_value',
                'is_active', 'archived_at', 'notes',
            ]);
        });
    }
};
