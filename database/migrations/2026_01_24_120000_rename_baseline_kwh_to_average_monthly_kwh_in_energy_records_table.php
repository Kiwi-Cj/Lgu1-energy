<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->renameColumn('baseline_kwh', 'average_monthly_kwh');
        });
    }

    public function down()
    {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->renameColumn('average_monthly_kwh', 'baseline_kwh');
        });
    }
};