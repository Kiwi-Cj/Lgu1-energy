<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('facility_meters')) {
            return;
        }

        Schema::table('facility_meters', function (Blueprint $table) {
            if (! Schema::hasColumn('facility_meters', 'baseline_kwh')) {
                $table->decimal('baseline_kwh', 14, 2)->nullable()->after('multiplier');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('facility_meters')) {
            return;
        }

        Schema::table('facility_meters', function (Blueprint $table) {
            if (Schema::hasColumn('facility_meters', 'baseline_kwh')) {
                $table->dropColumn('baseline_kwh');
            }
        });
    }
};
