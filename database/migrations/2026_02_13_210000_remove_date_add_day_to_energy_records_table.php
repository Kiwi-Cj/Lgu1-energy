<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (Schema::hasColumn('energy_records', 'date')) {
                $table->dropColumn('date');
            }
            if (!Schema::hasColumn('energy_records', 'day')) {
                $table->integer('day')->nullable()->after('month');
            }
        });
    }

    public function down()
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (!Schema::hasColumn('energy_records', 'date')) {
                $table->date('date')->nullable()->after('month');
            }
            if (Schema::hasColumn('energy_records', 'day')) {
                $table->dropColumn('day');
            }
        });
    }
};
