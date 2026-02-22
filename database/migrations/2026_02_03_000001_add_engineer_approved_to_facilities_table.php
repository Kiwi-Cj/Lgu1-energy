<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('facilities')) {
            return;
        }

        Schema::table('facilities', function (Blueprint $table) {
            if (!Schema::hasColumn('facilities', 'engineer_approved')) {
                $table->boolean('engineer_approved')->default(false);
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('facilities')) {
            return;
        }

        Schema::table('facilities', function (Blueprint $table) {
            if (Schema::hasColumn('facilities', 'engineer_approved')) {
                $table->dropColumn('engineer_approved');
            }
        });
    }
};
