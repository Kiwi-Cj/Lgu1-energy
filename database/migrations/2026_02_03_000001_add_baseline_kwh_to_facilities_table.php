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
        if (!Schema::hasTable('facilities')) {
            return;
        }

        // Only add column if it does not exist
        if (!Schema::hasColumn('facilities', 'baseline_kwh')) {
            Schema::table('facilities', function (Blueprint $table) {
                $table->float('baseline_kwh')->nullable()->after('floor_area');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('facilities')) {
            return;
        }

        Schema::table('facilities', function (Blueprint $table) {
            if (Schema::hasColumn('facilities', 'baseline_kwh')) {
                $table->dropColumn('baseline_kwh');
            }
        });
    }
};
