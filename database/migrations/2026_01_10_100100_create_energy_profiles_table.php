<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('energy_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_id');
            $table->string('electric_meter_no');
            $table->string('utility_provider');
            $table->string('contract_account_no');
            $table->decimal('average_monthly_kwh', 10, 2);
            $table->string('main_energy_source');
            $table->string('backup_power');
            $table->string('transformer_capacity')->nullable();
            $table->integer('number_of_meters');
            $table->timestamps();

            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_profiles');
    }
};
