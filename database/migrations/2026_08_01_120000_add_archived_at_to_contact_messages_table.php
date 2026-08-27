<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('contact_messages', 'archived_at')) {
            Schema::table('contact_messages', function (Blueprint $table): void {
                $table->timestamp('archived_at')->nullable()->after('read_by_user_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('contact_messages', 'archived_at')) {
            Schema::table('contact_messages', function (Blueprint $table): void {
                $table->dropIndex(['archived_at']);
                $table->dropColumn('archived_at');
            });
        }
    }
};
