<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('energy_records')) {
            return;
        }

        Schema::table('energy_records', function (Blueprint $table) {
            if (! Schema::hasColumn('energy_records', 'rate_per_kwh')) {
                $table->decimal('rate_per_kwh', 10, 2)->nullable()->after('energy_cost');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('energy_records')) {
            return;
        }

        Schema::table('energy_records', function (Blueprint $table) {
            if (Schema::hasColumn('energy_records', 'rate_per_kwh')) {
                $table->dropColumn('rate_per_kwh');
            }
        });
    }
};

