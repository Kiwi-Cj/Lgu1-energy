<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('energy_incidents') && ! Schema::hasColumn('energy_incidents', 'severity')) {
            Schema::table('energy_incidents', function (Blueprint $table) {
                $table->string('severity', 30)->nullable()->after('deviation_percent');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('energy_incidents') && Schema::hasColumn('energy_incidents', 'severity')) {
            Schema::table('energy_incidents', function (Blueprint $table) {
                $table->dropColumn('severity');
            });
        }
    }
};
