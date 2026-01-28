<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (Schema::hasColumn('energy_records', 'kwh_consumed') && !Schema::hasColumn('energy_records', 'actual_kwh')) {
                $table->renameColumn('kwh_consumed', 'actual_kwh');
            }
        });
    }

    public function down(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (Schema::hasColumn('energy_records', 'actual_kwh')) {
                $table->renameColumn('actual_kwh', 'kwh_consumed');
            }
        });
    }
};
