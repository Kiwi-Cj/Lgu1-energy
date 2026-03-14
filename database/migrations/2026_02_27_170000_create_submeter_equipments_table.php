<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('submeter_equipments')) {
            return;
        }

        Schema::create('submeter_equipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submeter_id')->constrained('submeters')->cascadeOnDelete();
            $table->string('equipment_name');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('rated_watts', 12, 2);
            $table->decimal('operating_hours_per_day', 6, 2);
            $table->unsignedSmallInteger('operating_days_per_month');
            $table->decimal('estimated_kwh', 14, 2)->storedAs(
                '(rated_watts * quantity * operating_hours_per_day * operating_days_per_month) / 1000'
            );
            $table->timestamps();

            $table->index(['submeter_id', 'equipment_name'], 'submeter_equipment_lookup_idx');
            $table->unique(['submeter_id', 'equipment_name'], 'submeter_equipment_unique_name');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('submeter_equipments')) {
            Schema::dropIfExists('submeter_equipments');
        }
    }
};

