<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('energy_records')) {
            return;
        }

        Schema::table('energy_records', function (Blueprint $table) {
            if (!Schema::hasColumn('energy_records', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('energy_records')) {
            return;
        }

        Schema::table('energy_records', function (Blueprint $table) {
            if (Schema::hasColumn('energy_records', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
