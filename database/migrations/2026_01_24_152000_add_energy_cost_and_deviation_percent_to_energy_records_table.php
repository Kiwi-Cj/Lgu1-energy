<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->decimal('energy_cost', 12, 2)->nullable()->after('rate_per_kwh');
            $table->decimal('deviation_percent', 6, 2)->nullable()->after('energy_cost');
        });
    }

    public function down(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->dropColumn(['energy_cost', 'deviation_percent']);
        });
    }
};
