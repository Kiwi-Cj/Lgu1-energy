<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('facility_meter_weekly_readings')) {
            return;
        }

        Schema::create('facility_meter_weekly_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->foreignId('meter_id')->constrained('facility_meters')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedTinyInteger('week_number');
            $table->date('reading_date')->nullable();
            $table->decimal('previous_reading_kwh', 14, 2)->nullable();
            $table->decimal('current_reading_kwh', 14, 2)->nullable();
            $table->decimal('actual_kwh', 14, 2);
            $table->decimal('rate_per_kwh', 10, 2)->default(12.00);
            $table->decimal('cost', 14, 2)->default(0);
            $table->string('notes', 500)->nullable();
            $table->foreignId('encoded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['facility_id', 'meter_id', 'year', 'month', 'week_number'],
                'facility_meter_weekly_unique'
            );
            $table->index(['facility_id', 'year', 'month'], 'facility_weekly_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_meter_weekly_readings');
    }
};
