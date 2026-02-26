<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('facilities')) {
            return;
        }

        Schema::table('facilities', function (Blueprint $table) {
            if (!Schema::hasColumn('facilities', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('facilities')) {
            return;
        }

        Schema::table('facilities', function (Blueprint $table) {
            if (Schema::hasColumn('facilities', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
