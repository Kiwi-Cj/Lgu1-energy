<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_energy_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->date('checklist_date');
            $table->string('task_key', 80);
            $table->string('task_label');
            $table->string('period', 20);
            $table->boolean('is_completed')->default(false);
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['facility_id', 'checklist_date', 'task_key'], 'daily_energy_checklist_unique');
            $table->index(['checklist_date', 'is_completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_energy_checklists');
    }
};
