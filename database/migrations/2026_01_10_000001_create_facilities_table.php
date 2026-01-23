<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Facility Name
            $table->string('type'); // Facility Type
            $table->string('department')->nullable(); // Department / Office
            $table->string('address'); // Address / Location
            $table->string('barangay'); // Barangay
            $table->float('floor_area')->nullable(); // Floor Area (sqm)
            $table->integer('floors')->nullable(); // Number of Floors
            $table->integer('year_built')->nullable(); // Year Built
            $table->string('operating_hours')->nullable(); // Operating Hours
            $table->string('image')->nullable(); // Facility Image
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('facilities');
    }
};
