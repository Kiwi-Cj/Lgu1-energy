<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('main_meter_alerts')) {
            return;
        }

        Schema::create('main_meter_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('main_meter_reading_id')->constrained('main_meter_readings')->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->decimal('baseline_kwh', 14, 2);
            $table->decimal('current_kwh', 14, 2);
            $table->decimal('increase_percent', 8, 2);
            $table->enum('alert_level', ['none', 'warning', 'critical'])->default('none');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['facility_id', 'alert_level', 'created_at'], 'main_meter_alert_facility_level_idx');
            $table->unique('main_meter_reading_id', 'main_meter_alert_per_reading_unique');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('main_meter_alerts')) {
            Schema::dropIfExists('main_meter_alerts');
        }
    }
};
