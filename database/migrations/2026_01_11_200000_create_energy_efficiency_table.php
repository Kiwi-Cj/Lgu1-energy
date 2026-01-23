<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('energy_efficiency', function (Blueprint $table) {
            $table->id();
            $table->string('facility');
            $table->string('month');
            $table->integer('year');
            $table->integer('actual_kwh');
            $table->integer('avg_kwh');
            $table->integer('variance');
            $table->float('eui', 8, 2);
            $table->string('rating');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_efficiency');
    }
};
