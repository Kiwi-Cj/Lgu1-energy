<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (! Schema::hasColumn('energy_records', 'review_status')) {
                $table->string('review_status', 20)->default('for_review')->index()->after('input_source');
            }
            if (! Schema::hasColumn('energy_records', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->index()->after('review_status');
            }
            if (! Schema::hasColumn('energy_records', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
            if (! Schema::hasColumn('energy_records', 'review_remarks')) {
                $table->text('review_remarks')->nullable()->after('reviewed_at');
            }
        });

        // Preserve the accepted state of records that predate the review workflow.
        DB::table('energy_records')
            ->whereNull('deleted_at')
            ->update([
                'review_status' => 'approved',
                'reviewed_at' => DB::raw('COALESCE(updated_at, created_at)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            foreach (['review_remarks', 'reviewed_at', 'reviewed_by', 'review_status'] as $column) {
                if (Schema::hasColumn('energy_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
