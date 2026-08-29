<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_energy_checklist_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('task_key', 80)->unique();
            $table->string('task_label');
            $table->string('period', 20);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['facility_id', 'period', 'is_active'], 'daily_checklist_task_scope_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_energy_checklist_tasks');
    }
};
