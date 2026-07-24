<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Facilities mirrored from the CPRF (facilities reservation) system carry
 * source='cprf' and external_ref = the CPRF facility id. Locally created
 * facilities keep source='local' with a NULL external_ref, so the unique
 * (source, external_ref) index only constrains mirrored rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            if (! Schema::hasColumn('facilities', 'source')) {
                $table->string('source', 20)->default('local')->index();
            }
            if (! Schema::hasColumn('facilities', 'external_ref')) {
                $table->unsignedBigInteger('external_ref')->nullable();
            }
        });

        if (! Schema::hasIndex('facilities', 'facilities_source_external_ref_unique')) {
            Schema::table('facilities', function (Blueprint $table) {
                $table->unique(['source', 'external_ref'], 'facilities_source_external_ref_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropUnique('facilities_source_external_ref_unique');
        });

        Schema::table('facilities', function (Blueprint $table) {
            if (Schema::hasColumn('facilities', 'source')) {
                $table->dropColumn('source');
            }
            if (Schema::hasColumn('facilities', 'external_ref')) {
                $table->dropColumn('external_ref');
            }
        });
    }
};
