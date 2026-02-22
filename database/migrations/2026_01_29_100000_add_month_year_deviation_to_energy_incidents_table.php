<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('energy_incidents')) {
            return;
        }

        Schema::table('energy_incidents', function (Blueprint $table) {
            if (!Schema::hasColumn('energy_incidents', 'month')) {
                $table->integer('month')->nullable()->after('facility_id');
            }
            if (!Schema::hasColumn('energy_incidents', 'year')) {
                $table->integer('year')->nullable()->after('month');
            }
            if (!Schema::hasColumn('energy_incidents', 'deviation_percent')) {
                $table->decimal('deviation_percent', 8, 2)->nullable()->after('year');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('energy_incidents')) {
            return;
        }

        Schema::table('energy_incidents', function (Blueprint $table) {
            if (Schema::hasColumn('energy_incidents', 'deviation_percent')) {
                $table->dropColumn('deviation_percent');
            }
            if (Schema::hasColumn('energy_incidents', 'year')) {
                $table->dropColumn('year');
            }
            if (Schema::hasColumn('energy_incidents', 'month')) {
                $table->dropColumn('month');
            }
        });
    }
};
