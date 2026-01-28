<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('energy_records');
        Schema::create('energy_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_id');
            $table->integer('year');
            $table->integer('month');
            $table->decimal('actual_kwh', 12, 2);
            $table->decimal('energy_cost', 12, 2);
            $table->boolean('alert_flag')->default(0); // Added for alert logic
            $table->string('bill_image')->nullable();
            $table->unsignedBigInteger('recorded_by');
            $table->timestamps();

            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_records');
    }
};
