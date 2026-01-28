<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->renameColumn('alert_flag', 'alert');
        });
    }
    public function down() {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->renameColumn('alert', 'alert_flag');
        });
    }
};
