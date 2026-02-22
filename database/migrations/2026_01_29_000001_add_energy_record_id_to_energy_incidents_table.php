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
        if (!Schema::hasTable('energy_incidents')) {
            return;
        }

        Schema::table('energy_incidents', function (Blueprint $table) {
            if (Schema::hasColumn('energy_incidents', 'energy_record_id')) {
                try {
                    $table->dropForeign(['energy_record_id']);
                } catch (\Throwable $e) {
                    // Ignore if foreign key does not exist.
                }
                $table->dropColumn('energy_record_id');
            }
        });
    }
};
