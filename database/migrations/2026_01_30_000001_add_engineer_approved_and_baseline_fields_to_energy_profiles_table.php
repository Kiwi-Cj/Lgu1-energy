<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('energy_profiles')) {
            return;
        }

        Schema::table('energy_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('energy_profiles', 'engineer_approved')) {
                $table->boolean('engineer_approved')->default(false)->after('average_monthly_kwh');
            }
            if (!Schema::hasColumn('energy_profiles', 'baseline_locked')) {
                $table->boolean('baseline_locked')->default(false)->after('engineer_approved');
            }
            if (!Schema::hasColumn('energy_profiles', 'baseline_source')) {
                $table->string('baseline_source')->nullable()->after('baseline_locked');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('energy_profiles')) {
            return;
        }

        Schema::table('energy_profiles', function (Blueprint $table) {
            $drops = [];
            foreach (['engineer_approved', 'baseline_locked', 'baseline_source'] as $column) {
                if (Schema::hasColumn('energy_profiles', $column)) {
                    $drops[] = $column;
                }
            }
            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
