<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('energy_profiles')) {
            return;
        }

        Schema::table('energy_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('energy_profiles', 'primary_meter_id')) {
                $table->unsignedBigInteger('primary_meter_id')->nullable()->after('facility_id');
                $table->index('primary_meter_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('energy_profiles')) {
            return;
        }

        Schema::table('energy_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('energy_profiles', 'primary_meter_id')) {
                $table->dropColumn('primary_meter_id');
            }
        });
    }
};
