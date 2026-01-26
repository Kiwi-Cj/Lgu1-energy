<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->decimal('rate_per_kwh', 8, 2)->nullable()->after('actual_kwh');
        });
    }

    public function down(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->dropColumn('rate_per_kwh');
        });
    }
};
