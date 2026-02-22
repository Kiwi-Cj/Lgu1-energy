<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('energy_profiles')) {
            return;
        }

        Schema::table('energy_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('energy_profiles', 'average_monthly_kwh') && !Schema::hasColumn('energy_profiles', 'baseline_kwh')) {
                $table->renameColumn('average_monthly_kwh', 'baseline_kwh');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('energy_profiles')) {
            return;
        }

        Schema::table('energy_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('energy_profiles', 'baseline_kwh') && !Schema::hasColumn('energy_profiles', 'average_monthly_kwh')) {
                $table->renameColumn('baseline_kwh', 'average_monthly_kwh');
            }
        });
    }
};
