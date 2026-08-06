<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            if (! Schema::hasColumn('facilities', 'source_key')) {
                $table->string('source_key', 191)->nullable()->after('external_ref');
            }
        });

        if (! Schema::hasIndex('facilities', 'facilities_source_source_key_unique')) {
            Schema::table('facilities', function (Blueprint $table) {
                $table->unique(['source', 'source_key'], 'facilities_source_source_key_unique');
            });
        }

        Schema::table('energy_records', function (Blueprint $table) {
            if (! Schema::hasColumn('energy_records', 'external_source')) {
                $table->string('external_source', 30)->nullable()->after('input_source');
            }
            if (! Schema::hasColumn('energy_records', 'external_record_id')) {
                $table->string('external_record_id', 100)->nullable()->after('external_source');
            }
        });

        if (! Schema::hasIndex('energy_records', 'energy_records_external_identity_unique')) {
            Schema::table('energy_records', function (Blueprint $table) {
                $table->unique(['external_source', 'external_record_id'], 'energy_records_external_identity_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (Schema::hasIndex('energy_records', 'energy_records_external_identity_unique')) {
                $table->dropUnique('energy_records_external_identity_unique');
            }
            foreach (['external_record_id', 'external_source'] as $column) {
                if (Schema::hasColumn('energy_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('facilities', function (Blueprint $table) {
            if (Schema::hasIndex('facilities', 'facilities_source_source_key_unique')) {
                $table->dropUnique('facilities_source_source_key_unique');
            }
            if (Schema::hasColumn('facilities', 'source_key')) {
                $table->dropColumn('source_key');
            }
        });
    }
};
