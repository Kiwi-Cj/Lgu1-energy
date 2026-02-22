<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('maintenance')) {
            return;
        }

        Schema::table('maintenance', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance', 'trend')) {
                $table->string('trend')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('maintenance')) {
            return;
        }

        Schema::table('maintenance', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance', 'trend')) {
                $table->string('trend')->nullable(false)->change();
            }
        });
    }
};
