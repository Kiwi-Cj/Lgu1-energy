<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('daily_checklist_items')) {
            Schema::create('daily_checklist_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('facility_id')->nullable()->constrained('facilities')->nullOnDelete();
                $table->string('issue_type')->nullable();
                $table->string('trigger_month')->nullable();
                $table->string('maintenance_status')->nullable();
                $table->date('scheduled_date')->nullable();
                $table->string('assigned_to')->nullable();
                $table->date('completed_date')->nullable();
                $table->string('proof_photo_path')->nullable();
                $table->string('photo_requirement')->default('Optional');
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_checklist_items');
    }
};
