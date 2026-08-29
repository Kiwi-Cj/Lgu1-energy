<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('maintenance_history') && ! Schema::hasColumn('maintenance_history', 'trigger_date')) {
            Schema::table('maintenance_history', function (Blueprint $table) {
                $table->date('trigger_date')->nullable()->after('trigger_month');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('maintenance_history') && Schema::hasColumn('maintenance_history', 'trigger_date')) {
            Schema::table('maintenance_history', function (Blueprint $table) {
                $table->dropColumn('trigger_date');
            });
        }
    }
};
