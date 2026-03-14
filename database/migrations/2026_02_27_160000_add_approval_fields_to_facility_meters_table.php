<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('facility_meters')) {
            return;
        }

        Schema::table('facility_meters', function (Blueprint $table) {
            if (! Schema::hasColumn('facility_meters', 'approved_by_user_id')) {
                $table->unsignedBigInteger('approved_by_user_id')->nullable()->after('archive_reason')->index();
            }

            if (! Schema::hasColumn('facility_meters', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by_user_id')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('facility_meters')) {
            return;
        }

        Schema::table('facility_meters', function (Blueprint $table) {
            if (Schema::hasColumn('facility_meters', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('facility_meters', 'approved_by_user_id')) {
                $table->dropColumn('approved_by_user_id');
            }
        });
    }
};

