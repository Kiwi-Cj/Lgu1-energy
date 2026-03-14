<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('submeter_baselines')) {
            return;
        }

        DB::statement(
            "ALTER TABLE `submeter_baselines` MODIFY `baseline_type` ENUM(
                'moving_avg_3',
                'moving_avg_6',
                'seasonal_month',
                'normalized_per_sqm',
                'normalized_per_day',
                'equipment_estimate'
            ) NOT NULL"
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('submeter_baselines')) {
            return;
        }

        DB::table('submeter_baselines')
            ->whereIn('baseline_type', ['normalized_per_day', 'equipment_estimate'])
            ->delete();

        DB::statement(
            "ALTER TABLE `submeter_baselines` MODIFY `baseline_type` ENUM(
                'moving_avg_3',
                'moving_avg_6',
                'seasonal_month',
                'normalized_per_sqm'
            ) NOT NULL"
        );
    }
};
