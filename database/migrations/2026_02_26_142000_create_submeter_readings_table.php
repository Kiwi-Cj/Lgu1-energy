<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('submeter_readings')) {
            return;
        }

        Schema::create('submeter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submeter_id')->constrained('submeters')->cascadeOnDelete();
            $table->enum('period_type', ['daily', 'weekly', 'monthly'])->default('monthly');
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->decimal('reading_start_kwh', 14, 2);
            $table->decimal('reading_end_kwh', 14, 2);
            $table->decimal('kwh_used', 14, 2)->storedAs('reading_end_kwh - reading_start_kwh');
            $table->unsignedInteger('operating_days')->nullable();
            $table->foreignId('encoded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_engineer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['submeter_id', 'period_type', 'period_end_date'], 'submeter_reading_period_idx');
            $table->index(['approved_at', 'period_end_date'], 'submeter_reading_approval_idx');
            $table->unique(
                ['submeter_id', 'period_type', 'period_start_date', 'period_end_date'],
                'submeter_period_unique'
            );
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('submeter_readings')) {
            Schema::dropIfExists('submeter_readings');
        }
    }
};

