<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('submeter_equipment_files')) {
            return;
        }

        Schema::create('submeter_equipment_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submeter_equipment_id')->constrained('submeter_equipments')->cascadeOnDelete();
            $table->enum('meter_scope', ['sub', 'main'])->default('sub');
            $table->foreignId('submeter_id')->nullable()->constrained('submeters')->nullOnDelete();
            $table->foreignId('facility_meter_id')->nullable()->constrained('facility_meters')->nullOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('storage_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['meter_scope', 'submeter_id'], 'submeter_equipment_file_sub_idx');
            $table->index(['meter_scope', 'facility_meter_id'], 'submeter_equipment_file_main_idx');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('submeter_equipment_files')) {
            Schema::dropIfExists('submeter_equipment_files');
        }
    }
};

