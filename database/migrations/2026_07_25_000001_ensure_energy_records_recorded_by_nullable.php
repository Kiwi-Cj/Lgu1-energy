<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * energy_records.recorded_by is declared nullable in the original
 * create_core_tables migration, but CPRF-pushed readings (which have no
 * energy-app user to attribute) were failing to insert in production with
 * "Field 'recorded_by' doesn't have a default value" — the classic MySQL
 * strict-mode error for a NOT NULL column with no default. This normalizes
 * the column defensively in case production drifted from the migration
 * (doctrine/dbal isn't installed, so this uses a raw ALTER instead of
 * Schema::table()->change()). No-op on SQLite, which already allows NULL
 * here and has no equivalent MODIFY statement.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE energy_records MODIFY recorded_by BIGINT UNSIGNED NULL DEFAULT NULL');
    }

    public function down(): void
    {
        // Intentionally left nullable — reverting to NOT NULL could break
        // existing CPRF-sourced rows that now have a NULL recorded_by.
    }
};
