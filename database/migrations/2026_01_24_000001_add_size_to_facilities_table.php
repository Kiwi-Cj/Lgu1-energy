<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->enum('size', ['small', 'medium', 'large', 'extralarge'])->default('small')->after('type');
        });
    }

    public function down()
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn('size');
        });
    }
};
