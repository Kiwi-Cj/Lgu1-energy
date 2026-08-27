<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('energy_profiles')) {
            return;
        }

        $duplicateFacilityIds = DB::table('energy_profiles')
            ->whereNotNull('facility_id')
            ->groupBy('facility_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('facility_id');

        if ($duplicateFacilityIds->isNotEmpty()) {
            throw new \RuntimeException(
                'Cannot enforce one Energy Profile per facility. Resolve duplicate facility IDs first: '
                .$duplicateFacilityIds->implode(', ')
            );
        }

        Schema::table('energy_profiles', function (Blueprint $table) {
            $table->unique('facility_id', 'energy_profiles_facility_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('energy_profiles')) {
            return;
        }

        Schema::table('energy_profiles', function (Blueprint $table) {
            $table->dropUnique('energy_profiles_facility_unique');
        });
    }
};
