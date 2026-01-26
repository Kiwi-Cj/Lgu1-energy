<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (!Schema::hasColumn('energy_records', 'baseline_kwh')) {
                $table->decimal('baseline_kwh', 12, 2)->nullable()->after('kwh_consumed');
            }
            if (!Schema::hasColumn('energy_records', 'variance')) {
                $table->decimal('variance', 12, 2)->nullable()->after('baseline_kwh');
            }
            if (!Schema::hasColumn('energy_records', 'alert_level')) {
                $table->enum('alert_level', ['Normal', 'Medium', 'High'])->default('Normal')->after('variance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (Schema::hasColumn('energy_records', 'baseline_kwh')) {
                $table->dropColumn('baseline_kwh');
            }
            if (Schema::hasColumn('energy_records', 'variance')) {
                $table->dropColumn('variance');
            }
            if (Schema::hasColumn('energy_records', 'alert_level')) {
                $table->dropColumn('alert_level');
            }
        });
    }
};