<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('energy_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_id');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('kwh_consumed', 10, 2);
            // Remove peak_load for now since not in DB
            $table->timestamps();

            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_records');
    }
};
