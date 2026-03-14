<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('submeter_baselines')) {
            return;
        }

        Schema::create('submeter_baselines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submeter_id')->constrained('submeters')->cascadeOnDelete();
            $table->enum('baseline_type', ['moving_avg_3', 'moving_avg_6', 'seasonal_month', 'normalized_per_sqm']);
            $table->unsignedTinyInteger('months_window')->nullable();
            $table->decimal('baseline_value_kwh', 14, 2);
            $table->decimal('baseline_value_normalized', 14, 4)->nullable();
            $table->string('computed_for_period', 32);
            $table->timestamp('computed_at');
            $table->timestamps();

            $table->index(['submeter_id', 'baseline_type', 'computed_for_period'], 'submeter_baseline_lookup_idx');
            $table->unique(
                ['submeter_id', 'baseline_type', 'computed_for_period'],
                'submeter_baseline_unique'
            );
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('submeter_baselines')) {
            Schema::dropIfExists('submeter_baselines');
        }
    }
};

