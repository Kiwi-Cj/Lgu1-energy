<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('energy_records')) {
            return;
        }

        Schema::table('energy_records', function (Blueprint $table) {
            if (!Schema::hasColumn('energy_records', 'baseline_kwh')) {
                $table->float('baseline_kwh')->nullable()->after('actual_kwh');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('energy_records')) {
            return;
        }

        Schema::table('energy_records', function (Blueprint $table) {
            if (Schema::hasColumn('energy_records', 'baseline_kwh')) {
                $table->dropColumn('baseline_kwh');
            }
        });
    }
};
