<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('energy_incidents')) {
            Schema::table('energy_incidents', function (Blueprint $table) {
                if (! Schema::hasColumn('energy_incidents', 'category')) {
                    $table->string('category')->nullable()->after('facility_id');
                }
                if (! Schema::hasColumn('energy_incidents', 'source')) {
                    $table->string('source', 30)->default('auto')->after('category');
                }
                if (! Schema::hasColumn('energy_incidents', 'affected_asset')) {
                    $table->string('affected_asset')->nullable()->after('description');
                }
                if (! Schema::hasColumn('energy_incidents', 'evidence_path')) {
                    $table->string('evidence_path')->nullable()->after('affected_asset');
                }
                if (! Schema::hasColumn('energy_incidents', 'detected_at')) {
                    $table->dateTime('detected_at')->nullable()->after('date_detected');
                }
            });
        }

        if (Schema::hasTable('maintenance') && ! Schema::hasColumn('maintenance', 'energy_incident_id')) {
            Schema::table('maintenance', function (Blueprint $table) {
                $table->unsignedBigInteger('energy_incident_id')->nullable()->after('energy_record_id');
                $table->index('energy_incident_id', 'maintenance_energy_incident_id_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('maintenance') && Schema::hasColumn('maintenance', 'energy_incident_id')) {
            Schema::table('maintenance', function (Blueprint $table) {
                $table->dropIndex('maintenance_energy_incident_id_index');
                $table->dropColumn('energy_incident_id');
            });
        }

        if (Schema::hasTable('energy_incidents')) {
            Schema::table('energy_incidents', function (Blueprint $table) {
                foreach (['category', 'source', 'affected_asset', 'evidence_path', 'detected_at'] as $column) {
                    if (Schema::hasColumn('energy_incidents', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
