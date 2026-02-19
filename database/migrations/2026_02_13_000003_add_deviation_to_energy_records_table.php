<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (!Schema::hasColumn('energy_records', 'deviation')) {
                $table->decimal('deviation', 8, 2)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->dropColumn('deviation');
        });
    }
};
