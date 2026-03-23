<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'notifications_user_created_at_idx');
            $table->index(['user_id', 'read_at'], 'notifications_user_read_at_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_created_at_idx');
            $table->dropIndex('notifications_user_read_at_idx');
        });
    }
};
