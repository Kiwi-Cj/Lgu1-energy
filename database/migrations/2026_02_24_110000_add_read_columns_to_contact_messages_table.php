<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('contact_messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('email_error');
            }

            if (! Schema::hasColumn('contact_messages', 'read_by_user_id')) {
                $table->unsignedBigInteger('read_by_user_id')->nullable()->after('read_at');
                $table->index('read_by_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            if (Schema::hasColumn('contact_messages', 'read_by_user_id')) {
                $table->dropIndex(['read_by_user_id']);
                $table->dropColumn('read_by_user_id');
            }

            if (Schema::hasColumn('contact_messages', 'read_at')) {
                $table->dropColumn('read_at');
            }
        });
    }
};
