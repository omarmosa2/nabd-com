<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->string('location')->nullable()->after('description');
            $table->string('phone', 30)->nullable()->after('location');
            $table->string('status', 32)->default('active')->after('phone');
            $table->timestamp('archived_at')->nullable()->after('status');

            $table->index('status', 'clinics_status_idx');
            $table->index('name', 'clinics_name_idx');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropIndex('clinics_status_idx');
            $table->dropIndex('clinics_name_idx');
            $table->dropColumn(['description', 'location', 'phone', 'status', 'archived_at']);
        });
    }
};
