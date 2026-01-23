<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->decimal('kwh_vs_avg', 10, 2)->nullable()->after('kwh_consumed');
            $table->decimal('percent_change', 8, 2)->nullable()->after('kwh_vs_avg');
        });
    }

    public function down(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->dropColumn(['kwh_vs_avg', 'percent_change']);
        });
    }
};