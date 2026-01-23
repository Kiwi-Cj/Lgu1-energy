<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role')->default('user');
            $table->string('department')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->timestamp('last_login')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
