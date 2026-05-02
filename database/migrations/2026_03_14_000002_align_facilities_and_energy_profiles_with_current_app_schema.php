<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('facilities')) {
            Schema::table('facilities', function (Blueprint $table) {
                if (! Schema::hasColumn('facilities', 'address')) {
                    $table->string('address')->nullable()->after('type');
                }

                if (! Schema::hasColumn('facilities', 'barangay')) {
                    $table->string('barangay')->nullable()->after('address');
                }

                if (! Schema::hasColumn('facilities', 'department')) {
                    $table->string('department')->nullable()->after('type');
                }

                if (! Schema::hasColumn('facilities', 'floors')) {
                    $table->unsignedInteger('floors')->nullable()->after('floor_area_sqm');
                }

                if (! Schema::hasColumn('facilities', 'year_built')) {
                    $table->unsignedInteger('year_built')->nullable()->after('floors');
                }

                if (! Schema::hasColumn('facilities', 'operating_hours')) {
                    $table->string('operating_hours')->nullable()->after('year_built');
                }

                if (! Schema::hasColumn('facilities', 'baseline_status')) {
                    $table->string('baseline_status')->nullable()->after('baseline_kwh');
                }

                if (! Schema::hasColumn('facilities', 'baseline_start_date')) {
                    $table->date('baseline_start_date')->nullable()->after('baseline_status');
                }
            });
        }

        if (Schema::hasTable('energy_profiles')) {
            Schema::table('energy_profiles', function (Blueprint $table) {
                if (! Schema::hasColumn('energy_profiles', 'electric_meter_no')) {
                    $table->string('electric_meter_no')->nullable()->after('primary_meter_id');
                }

                if (! Schema::hasColumn('energy_profiles', 'utility_provider')) {
                    $table->string('utility_provider')->nullable()->after('electric_meter_no');
                }

                if (! Schema::hasColumn('energy_profiles', 'contract_account_no')) {
                    $table->string('contract_account_no')->nullable()->after('utility_provider');
                }

                if (! Schema::hasColumn('energy_profiles', 'main_energy_source')) {
                    $table->string('main_energy_source')->nullable()->after('contract_account_no');
                }

                if (! Schema::hasColumn('energy_profiles', 'backup_power')) {
                    $table->string('backup_power')->nullable()->after('main_energy_source');
                }

                if (! Schema::hasColumn('energy_profiles', 'transformer_capacity')) {
                    $table->string('transformer_capacity')->nullable()->after('backup_power');
                }

                if (! Schema::hasColumn('energy_profiles', 'number_of_meters')) {
                    $table->unsignedInteger('number_of_meters')->nullable()->after('transformer_capacity');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('energy_profiles')) {
            Schema::table('energy_profiles', function (Blueprint $table) {
                foreach ([
                    'number_of_meters',
                    'transformer_capacity',
                    'backup_power',
                    'main_energy_source',
                    'contract_account_no',
                    'utility_provider',
                    'electric_meter_no',
                ] as $column) {
                    if (Schema::hasColumn('energy_profiles', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('facilities')) {
            Schema::table('facilities', function (Blueprint $table) {
                foreach ([
                    'baseline_start_date',
                    'baseline_status',
                    'operating_hours',
                    'year_built',
                    'floors',
                    'department',
                    'barangay',
                    'address',
                ] as $column) {
                    if (Schema::hasColumn('facilities', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
