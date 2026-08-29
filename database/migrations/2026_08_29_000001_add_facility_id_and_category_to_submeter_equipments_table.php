<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('submeter_equipments')) {
            return;
        }

        Schema::table('submeter_equipments', function (Blueprint $table) {
            if (! Schema::hasColumn('submeter_equipments', 'facility_id')) {
                $table->unsignedBigInteger('facility_id')->nullable()->after('id');
                $table->index('facility_id', 'submeter_equipments_facility_id_idx');
            }

            if (! Schema::hasColumn('submeter_equipments', 'category')) {
                $table->string('category', 100)->nullable()->after('equipment_name');
            }

            if (! Schema::hasColumn('submeter_equipments', 'location')) {
                $table->string('location', 191)->nullable()->after('category');
            }

            if (! Schema::hasColumn('submeter_equipments', 'notes')) {
                $table->text('notes')->nullable()->after('operating_days_per_month');
            }
        });

        // Add foreign key if not sqlite
        if (DB::connection()->getDriverName() !== 'sqlite') {
            try {
                Schema::table('submeter_equipments', function (Blueprint $table) {
                    $table->foreign('facility_id')
                        ->references('id')
                        ->on('facilities')
                        ->cascadeOnDelete();
                });
            } catch (\Throwable $e) {
                // Foreign key may already exist or fail gracefully
            }
        }

        // Backfill facility_id from facility_meters or submeters
        try {
            DB::statement("
                UPDATE submeter_equipments se
                JOIN facility_meters fm ON se.facility_meter_id = fm.id
                SET se.facility_id = fm.facility_id
                WHERE se.facility_id IS NULL AND se.facility_meter_id IS NOT NULL
            ");

            DB::statement("
                UPDATE submeter_equipments se
                JOIN submeters sm ON se.submeter_id = sm.id
                SET se.facility_id = sm.facility_id
                WHERE se.facility_id IS NULL AND se.submeter_id IS NOT NULL
            ");
        } catch (\Throwable $e) {
            // Ignore backfill errors if tables empty
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('submeter_equipments')) {
            return;
        }

        Schema::table('submeter_equipments', function (Blueprint $table) {
            if (Schema::hasColumn('submeter_equipments', 'facility_id')) {
                try {
                    $table->dropForeign(['facility_id']);
                } catch (\Throwable $e) {}
                try {
                    $table->dropIndex('submeter_equipments_facility_id_idx');
                } catch (\Throwable $e) {}
                $table->dropColumn('facility_id');
            }

            if (Schema::hasColumn('submeter_equipments', 'category')) {
                $table->dropColumn('category');
            }

            if (Schema::hasColumn('submeter_equipments', 'location')) {
                $table->dropColumn('location');
            }

            if (Schema::hasColumn('submeter_equipments', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
