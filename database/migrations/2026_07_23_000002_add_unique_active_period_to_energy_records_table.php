<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard: refuse to add the unique index if active (non-soft-deleted)
        // duplicate periods already exist — the index would fail to create
        // (or, worse, silently misbehave) rather than surface the data
        // problem clearly.
        $dupes = DB::table('energy_records')
            ->selectRaw('facility_id, COALESCE(meter_id, 0) AS meter_key, year, month, COUNT(*) AS cnt')
            ->whereNull('deleted_at')
            ->groupBy('facility_id', 'meter_key', 'year', 'month')
            ->having('cnt', '>', 1)
            ->count();

        if ($dupes > 0) {
            throw new RuntimeException("Cannot add unique period index: {$dupes} duplicate active energy_records period group(s) exist. Resolve (archive or merge) the duplicates, then re-run this migration.");
        }

        Schema::table('energy_records', function (Blueprint $table) {
            // COALESCE(meter_id,0) for ACTIVE rows; NULL for soft-deleted rows.
            // NULLs are distinct in unique indexes on both MariaDB and sqlite, so
            // archived rows are exempt while active periods are enforced unique.
            $table->unsignedBigInteger('active_period_key')->nullable()
                ->storedAs('CASE WHEN deleted_at IS NULL THEN COALESCE(meter_id, 0) ELSE NULL END');
            $table->unique(['facility_id', 'active_period_key', 'year', 'month'], 'energy_records_active_period_unique');
        });
    }

    public function down(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->dropUnique('energy_records_active_period_unique');
            $table->dropColumn('active_period_key');
        });
    }
};
