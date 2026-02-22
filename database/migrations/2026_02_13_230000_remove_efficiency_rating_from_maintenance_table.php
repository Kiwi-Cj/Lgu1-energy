<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('maintenance')) {
            return;
        }

        Schema::table('maintenance', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance', 'efficiency_rating')) {
                $table->dropColumn('efficiency_rating');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('maintenance')) {
            return;
        }

        Schema::table('maintenance', function (Blueprint $table) {
            if (!Schema::hasColumn('maintenance', 'efficiency_rating')) {
                $table->string('efficiency_rating')->nullable();
            }
        });
    }
};
