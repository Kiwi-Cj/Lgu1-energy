<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('submeters')) {
            return;
        }

        Schema::create('submeters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->string('submeter_name');
            $table->enum('meter_type', ['single_phase', 'three_phase'])->default('single_phase');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['facility_id', 'status']);
            $table->unique(['facility_id', 'submeter_name']);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('submeters')) {
            Schema::dropIfExists('submeters');
        }
    }
};

