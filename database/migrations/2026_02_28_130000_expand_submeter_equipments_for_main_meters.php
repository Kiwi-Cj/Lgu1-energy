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
            if (! Schema::hasColumn('submeter_equipments', 'meter_scope')) {
                $table->enum('meter_scope', ['sub', 'main'])->default('sub')->after('submeter_id');
            }

            if (! Schema::hasColumn('submeter_equipments', 'facility_meter_id')) {
                $table->unsignedBigInteger('facility_meter_id')->nullable()->after('meter_scope');
                $table->index('facility_meter_id', 'submeter_equipment_main_meter_idx');
            }
        });

        $submeterColumn = DB::selectOne("SHOW COLUMNS FROM `submeter_equipments` LIKE 'submeter_id'");
        $isSubmeterNullable = isset($submeterColumn->Null) && strtoupper((string) $submeterColumn->Null) === 'YES';
        if (! $isSubmeterNullable) {
            Schema::table('submeter_equipments', function (Blueprint $table) {
                $table->dropForeign(['submeter_id']);
            });

            DB::statement('ALTER TABLE `submeter_equipments` MODIFY `submeter_id` BIGINT UNSIGNED NULL');

            Schema::table('submeter_equipments', function (Blueprint $table) {
                $table->foreign('submeter_id')
                    ->references('id')
                    ->on('submeters')
                    ->cascadeOnDelete();
            });
        }

        Schema::table('submeter_equipments', function (Blueprint $table) {
            $table->foreign('facility_meter_id')
                ->references('id')
                ->on('facility_meters')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('submeter_equipments')) {
            return;
        }

        if (Schema::hasColumn('submeter_equipments', 'facility_meter_id')) {
            Schema::table('submeter_equipments', function (Blueprint $table) {
                $table->dropForeign(['facility_meter_id']);
                $table->dropIndex('submeter_equipment_main_meter_idx');
                $table->dropColumn('facility_meter_id');
            });
        }

        if (Schema::hasColumn('submeter_equipments', 'meter_scope')) {
            Schema::table('submeter_equipments', function (Blueprint $table) {
                $table->dropColumn('meter_scope');
            });
        }

        $submeterColumn = DB::selectOne("SHOW COLUMNS FROM `submeter_equipments` LIKE 'submeter_id'");
        $isSubmeterNullable = isset($submeterColumn->Null) && strtoupper((string) $submeterColumn->Null) === 'YES';
        if ($isSubmeterNullable) {
            Schema::table('submeter_equipments', function (Blueprint $table) {
                $table->dropForeign(['submeter_id']);
            });

            DB::statement('ALTER TABLE `submeter_equipments` MODIFY `submeter_id` BIGINT UNSIGNED NOT NULL');

            Schema::table('submeter_equipments', function (Blueprint $table) {
                $table->foreign('submeter_id')
                    ->references('id')
                    ->on('submeters')
                    ->cascadeOnDelete();
            });
        }
    }
};

