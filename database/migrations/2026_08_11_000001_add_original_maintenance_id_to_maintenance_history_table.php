<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * maintenance_history rows are created fresh (own auto-increment id) when an
 * active `maintenance` row is archived on completion — see
 * MaintenanceSyncHelpers::applyMaintenancePostSaveEffects(). That means the
 * CIMM integration's "active" and "history" pulls of the same underlying
 * task have two different ids, so CIMM's dedup-by-id import logic couldn't
 * tell they were the same task and created a second maintenance_schedule row
 * every time a task was completed. This column preserves the original
 * `maintenance.id` so CIMM can key on a stable identity across that
 * transition instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('maintenance_history') && !Schema::hasColumn('maintenance_history', 'original_maintenance_id')) {
            Schema::table('maintenance_history', function (Blueprint $table) {
                $table->unsignedBigInteger('original_maintenance_id')->nullable()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('maintenance_history') && Schema::hasColumn('maintenance_history', 'original_maintenance_id')) {
            Schema::table('maintenance_history', function (Blueprint $table) {
                $table->dropColumn('original_maintenance_id');
            });
        }
    }
};
