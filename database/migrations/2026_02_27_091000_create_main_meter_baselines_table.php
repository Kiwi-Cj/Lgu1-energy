<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('main_meter_baselines')) {
            return;
        }

        Schema::create('main_meter_baselines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->enum('baseline_type', ['moving_avg_3', 'moving_avg_6', 'seasonal', 'normalized_per_day']);
            $table->decimal('baseline_kwh', 14, 2);
            $table->decimal('baseline_kwh_per_day', 14, 4);
            $table->decimal('baseline_peak_kw', 12, 2)->nullable();
            $table->string('computed_for_period', 32);
            $table->timestamp('computed_at');
            $table->timestamps();

            $table->index(['facility_id', 'computed_for_period'], 'main_meter_baseline_period_idx');
            $table->unique(
                ['facility_id', 'baseline_type', 'computed_for_period'],
                'main_meter_baseline_unique'
            );
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('main_meter_baselines')) {
            Schema::dropIfExists('main_meter_baselines');
        }
    }
};
