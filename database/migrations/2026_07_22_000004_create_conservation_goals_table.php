<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conservation_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('goal_type', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->enum('target_metric', ['maximum_kwh', 'reduction_percent', 'cost_savings']);
            $table->decimal('target_value', 14, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'achieved', 'failed', 'expired'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['start_date', 'end_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conservation_goals');
    }
};
