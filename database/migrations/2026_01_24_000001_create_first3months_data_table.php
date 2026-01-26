<?php
// database/migrations/2026_01_24_000001_create_first3months_data_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('first3months_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_id')->unique();
            $table->decimal('month1', 12, 2);
            $table->decimal('month2', 12, 2);
            $table->decimal('month3', 12, 2);
            $table->timestamps();

            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('first3months_data');
    }
};
