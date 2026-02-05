<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('energy_incidents', function (Blueprint $table) {
            if (!Schema::hasColumn('energy_incidents', 'energy_record_id')) {
                $table->unsignedBigInteger('energy_record_id')->nullable()->after('id');
                $table->foreign('energy_record_id')
                    ->references('id')->on('energy_records')
                    ->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('energy_incidents', function (Blueprint $table) {
            if (Schema::hasColumn('energy_incidents', 'energy_record_id')) {
                $table->dropForeign(['energy_record_id']);
                $table->dropColumn('energy_record_id');
            }
        });
    }
};
