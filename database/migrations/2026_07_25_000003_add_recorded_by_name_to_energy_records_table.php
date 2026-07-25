<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * recorded_by (FK to this app's own users table) is correctly NULL for
 * CPRF-pushed readings — no account here recorded them. CPRF already sends
 * the actual staff/admin name who recorded the reading on their side as
 * recorded_by_name (see CprfFacilityReadingController), previously only
 * logged and discarded. This gives it a real column so the attribution is
 * captured instead of thrown away. Plain ADD COLUMN IF NOT EXISTS — safe
 * on sqlite/mysql/mariadb alike, no driver-name branching needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (! Schema::hasColumn('energy_records', 'recorded_by_name')) {
                $table->string('recorded_by_name')->nullable()->after('recorded_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (Schema::hasColumn('energy_records', 'recorded_by_name')) {
                $table->dropColumn('recorded_by_name');
            }
        });
    }
};
