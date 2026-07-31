<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('maintenance')) {
            Schema::table('maintenance', function (Blueprint $table) {
                if (! Schema::hasIndex('maintenance', 'maintenance_status_scheduled_idx')) {
                    $table->index(['maintenance_status', 'scheduled_date'], 'maintenance_status_scheduled_idx');
                }
                if (! Schema::hasIndex('maintenance', 'maintenance_type_idx')) {
                    $table->index('maintenance_type', 'maintenance_type_idx');
                }
            });
        }

        if (Schema::hasTable('maintenance_history')) {
            Schema::table('maintenance_history', function (Blueprint $table) {
                if (! Schema::hasIndex('maintenance_history', 'maintenance_history_status_scheduled_idx')) {
                    $table->index(['maintenance_status', 'scheduled_date'], 'maintenance_history_status_scheduled_idx');
                }
                if (! Schema::hasIndex('maintenance_history', 'maintenance_history_type_idx')) {
                    $table->index('maintenance_type', 'maintenance_history_type_idx');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('maintenance')) {
            Schema::table('maintenance', function (Blueprint $table) {
                if (Schema::hasIndex('maintenance', 'maintenance_status_scheduled_idx')) {
                    $table->dropIndex('maintenance_status_scheduled_idx');
                }
                if (Schema::hasIndex('maintenance', 'maintenance_type_idx')) {
                    $table->dropIndex('maintenance_type_idx');
                }
            });
        }

        if (Schema::hasTable('maintenance_history')) {
            Schema::table('maintenance_history', function (Blueprint $table) {
                if (Schema::hasIndex('maintenance_history', 'maintenance_history_status_scheduled_idx')) {
                    $table->dropIndex('maintenance_history_status_scheduled_idx');
                }
                if (Schema::hasIndex('maintenance_history', 'maintenance_history_type_idx')) {
                    $table->dropIndex('maintenance_history_type_idx');
                }
            });
        }
    }
};
