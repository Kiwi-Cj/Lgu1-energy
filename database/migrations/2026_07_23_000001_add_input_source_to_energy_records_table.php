<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            // Where the record came from: 'manual' (this app's UI) or 'cprf'
            // (pushed by the facilities-reservation system). Same convention
            // as submeter_readings.input_source / main_meter_readings.
            $table->string('input_source', 20)->default('manual')->after('recorded_by');
        });
    }

    public function down(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->dropColumn('input_source');
        });
    }
};
