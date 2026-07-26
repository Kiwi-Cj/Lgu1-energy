<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('main_meter_readings')) {
            return;
        }

        Schema::create('main_meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->enum('period_type', ['monthly'])->default('monthly');
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->decimal('reading_start_kwh', 14, 2);
            $table->decimal('reading_end_kwh', 14, 2);
            $table->decimal('kwh_used', 14, 2)->storedAs('reading_end_kwh - reading_start_kwh');
            $table->unsignedInteger('operating_days')->nullable();
            $table->decimal('peak_demand_kw', 12, 2)->nullable();
            $table->decimal('power_factor', 5, 4)->nullable();
            $table->foreignId('encoded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['facility_id', 'period_end_date'], 'main_meter_facility_period_idx');
            $table->index(['approved_at', 'period_end_date'], 'main_meter_approval_period_idx');
            $table->unique(
                ['facility_id', 'period_type', 'period_start_date', 'period_end_date'],
                'main_meter_period_unique'
            );
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('main_meter_readings')) {
            Schema::dropIfExists('main_meter_readings');
        }
    }
};
