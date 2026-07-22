<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (! Schema::hasColumn('energy_records', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->index()->after('deleted_at');
            }
            if (! Schema::hasColumn('energy_records', 'archive_reason')) {
                $table->string('archive_reason', 500)->nullable()->after('deleted_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (Schema::hasColumn('energy_records', 'archive_reason')) {
                $table->dropColumn('archive_reason');
            }
            if (Schema::hasColumn('energy_records', 'deleted_by')) {
                $table->dropIndex(['deleted_by']);
                $table->dropColumn('deleted_by');
            }
        });
    }
};
