<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->deleteLegacyFacilityAggregateRecords();
        $this->dropLegacyTables();
    }

    public function down(): void
    {
        // Destructive cleanup migration; no automatic rollback.
    }

    private function deleteLegacyFacilityAggregateRecords(): void
    {
        if (! Schema::hasTable('energy_records') || ! Schema::hasColumn('energy_records', 'meter_id')) {
            return;
        }

        $legacyRecordIds = DB::table('energy_records')
            ->whereNull('meter_id')
            ->pluck('id');

        if ($legacyRecordIds->isEmpty()) {
            return;
        }

        if (Schema::hasTable('energy_incident_histories') && Schema::hasColumn('energy_incident_histories', 'energy_record_id')) {
            DB::table('energy_incident_histories')
                ->whereIn('energy_record_id', $legacyRecordIds)
                ->delete();
        }

        if (Schema::hasTable('energy_incidents') && Schema::hasColumn('energy_incidents', 'energy_record_id')) {
            DB::table('energy_incidents')
                ->whereIn('energy_record_id', $legacyRecordIds)
                ->delete();
        }

        if (Schema::hasTable('maintenance') && Schema::hasColumn('maintenance', 'energy_record_id')) {
            DB::table('maintenance')
                ->whereIn('energy_record_id', $legacyRecordIds)
                ->update(['energy_record_id' => null]);
        }

        DB::table('energy_records')
            ->whereIn('id', $legacyRecordIds)
            ->delete();
    }

    private function dropLegacyTables(): void
    {
        foreach (['first3months_data', 'energy_readings', 'bills', 'energy_efficiency'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }
    }
};

