<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (! Schema::hasColumn('energy_records', 'previous_reading_kwh')) {
                $table->decimal('previous_reading_kwh', 12, 2)->nullable()->after('actual_kwh');
            }
            if (! Schema::hasColumn('energy_records', 'current_reading_kwh')) {
                $table->decimal('current_reading_kwh', 12, 2)->nullable()->after('previous_reading_kwh');
            }
        });
    }

    public function down(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            foreach (['current_reading_kwh', 'previous_reading_kwh'] as $column) {
                if (Schema::hasColumn('energy_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
