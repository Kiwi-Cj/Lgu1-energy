<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('energy_saving_recommendations', function (Blueprint $table) {
            $table->foreignId('daily_checklist_task_id')
                ->nullable()
                ->unique()
                ->after('facility_id')
                ->constrained('daily_energy_checklist_tasks')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('energy_saving_recommendations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('daily_checklist_task_id');
        });
    }
};
