<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('energy_records')) {
            return;
        }

        Schema::table('energy_records', function (Blueprint $table) {
            if (Schema::hasColumn('energy_records', 'alert')) {
                $table->string('alert', 50)->nullable()->change();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('energy_records')) {
            return;
        }

        Schema::table('energy_records', function (Blueprint $table) {
            if (Schema::hasColumn('energy_records', 'alert')) {
                $table->tinyInteger('alert')->default(0)->change();
            }
        });
    }
};
