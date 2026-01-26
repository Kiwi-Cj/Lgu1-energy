<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->decimal('actual_kwh', 12, 2)->nullable()->after('year');
        });
    }

    public function down(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->dropColumn('actual_kwh');
        });
    }
};
