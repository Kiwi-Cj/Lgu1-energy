<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('energy_records')) {
            return;
        }

        Schema::table('energy_records', function (Blueprint $table) {
            if (!Schema::hasColumn('energy_records', 'meter_id')) {
                $table->unsignedBigInteger('meter_id')->nullable()->after('facility_id');
                $table->index('meter_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('energy_records')) {
            return;
        }

        Schema::table('energy_records', function (Blueprint $table) {
            if (Schema::hasColumn('energy_records', 'meter_id')) {
                $table->dropColumn('meter_id');
            }
        });
    }
};
