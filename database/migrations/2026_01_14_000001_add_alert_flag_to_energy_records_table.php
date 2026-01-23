<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->boolean('alert_flag')->default(0)->after('kwh_consumed');
        });
    }
    public function down() {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->dropColumn('alert_flag');
        });
    }
};
