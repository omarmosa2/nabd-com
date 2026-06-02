<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('reception')->after('email');
            $table->foreignId('clinic_id')->nullable()->after('role')->constrained('clinics')->nullOnDelete();
            $table->decimal('examination_fee', 10, 2)->default(0)->after('clinic_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropColumn(['role', 'clinic_id', 'examination_fee']);
        });
    }
};
