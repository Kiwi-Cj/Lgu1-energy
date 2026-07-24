<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * energy_records.energy_cost and rate_per_kwh are both declared nullable()
 * in their migrations, but production's actual columns enforce NOT NULL —
 * energy_cost confirmed by a failed CPRF facility-reading insert
 * ("Column 'energy_cost' cannot be null"), same drift class as
 * recorded_by (see 2026_07_25_000001). rate_per_kwh is fixed alongside it
 * pre-emptively: same decimal type, added in the same migration wave,
 * same likely origin (a manual-entry form requiring both together).
 *
 * The application code (CprfFacilityReadingController) now sends 0 instead
 * of null for both when CPRF doesn't report a cost/rate, so it no longer
 * depends on this migration running — this just brings the schema back in
 * line with what the migrations always intended. No-op on SQLite.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE energy_records MODIFY energy_cost DECIMAL(14,2) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE energy_records MODIFY rate_per_kwh DECIMAL(10,2) NULL DEFAULT NULL');
    }

    public function down(): void
    {
        // Intentionally left nullable — reverting to NOT NULL could break
        // existing CPRF-sourced rows that now have these as NULL.
    }
};
