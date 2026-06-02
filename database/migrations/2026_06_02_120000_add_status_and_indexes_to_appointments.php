<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('status', 32)->default('scheduled')->after('notes');
            $table->unsignedSmallInteger('duration_minutes')->default(30)->after('status');
            $table->foreignId('visit_id')
                ->nullable()
                ->after('duration_minutes')
                ->constrained('visits')
                ->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable()->after('visit_id');
            $table->timestamp('completed_at')->nullable()->after('cancelled_at');
            $table->string('cancel_reason')->nullable()->after('completed_at');

            $table->index(['doctor_id', 'appointment_date'], 'appointments_doctor_date_idx');
            $table->index('status', 'appointments_status_idx');
            $table->index('appointment_date', 'appointments_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['visit_id']);
            $table->dropIndex('appointments_doctor_date_idx');
            $table->dropIndex('appointments_status_idx');
            $table->dropIndex('appointments_date_idx');
            $table->dropColumn([
                'status',
                'duration_minutes',
                'visit_id',
                'cancelled_at',
                'completed_at',
                'cancel_reason',
            ]);
        });
    }
};
