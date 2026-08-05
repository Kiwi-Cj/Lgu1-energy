<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notifications') && ! Schema::hasColumn('notifications', 'acknowledged_at')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->timestamp('acknowledged_at')->nullable()->after('read_at')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'acknowledged_at')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropColumn('acknowledged_at');
            });
        }
    }
};
