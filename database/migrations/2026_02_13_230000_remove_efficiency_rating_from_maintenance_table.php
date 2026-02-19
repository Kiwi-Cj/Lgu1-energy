<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('maintenance', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance', 'efficiency_rating')) {
                $table->dropColumn('efficiency_rating');
            }
        });
    }

    public function down()
    {
        Schema::table('maintenance', function (Blueprint $table) {
            $table->string('efficiency_rating')->nullable();
        });
    }
};
