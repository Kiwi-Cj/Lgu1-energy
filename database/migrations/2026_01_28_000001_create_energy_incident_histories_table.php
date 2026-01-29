<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('energy_incident_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('energy_record_id');
            $table->string('alert_level');
            $table->decimal('deviation', 6, 2);
            $table->date('date_detected');
            $table->string('status')->default('Open');
            $table->timestamps();
            $table->foreign('energy_record_id')->references('id')->on('energy_records')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_incident_histories');
    }
};
