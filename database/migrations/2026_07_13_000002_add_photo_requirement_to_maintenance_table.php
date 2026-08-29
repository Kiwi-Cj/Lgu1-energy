<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('maintenance', 'photo_requirement')) {
            Schema::table('maintenance', function (Blueprint $table) {
                $table->string('photo_requirement')->default('Optional')->after('proof_photo_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('maintenance', 'photo_requirement')) {
            Schema::table('maintenance', function (Blueprint $table) {
                $table->dropColumn('photo_requirement');
            });
        }
    }
};
