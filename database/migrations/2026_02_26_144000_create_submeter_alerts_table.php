<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('submeter_alerts')) {
            return;
        }

        Schema::create('submeter_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submeter_reading_id')->constrained('submeter_readings')->cascadeOnDelete();
            $table->foreignId('submeter_id')->constrained('submeters')->cascadeOnDelete();
            $table->decimal('baseline_value_kwh', 14, 2);
            $table->decimal('current_value_kwh', 14, 2);
            $table->decimal('increase_percent', 8, 2);
            $table->enum('alert_level', ['none', 'warning', 'critical'])->default('none');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['alert_level', 'created_at'], 'submeter_alert_level_idx');
            $table->index(['submeter_id', 'created_at'], 'submeter_alert_submeter_idx');
            $table->unique('submeter_reading_id', 'submeter_alert_per_reading_unique');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('submeter_alerts')) {
            Schema::dropIfExists('submeter_alerts');
        }
    }
};

