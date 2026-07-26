<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('main_meter_readings')) {
            return;
        }

        Schema::table('main_meter_readings', function (Blueprint $table) {
            if (! Schema::hasColumn('main_meter_readings', 'input_source')) {
                $table->string('input_source', 20)->default('manual')->after('power_factor')->index();
            }

            if (! Schema::hasColumn('main_meter_readings', 'device_id')) {
                $table->string('device_id')->nullable()->after('input_source')->index();
            }

            if (! Schema::hasColumn('main_meter_readings', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('device_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('main_meter_readings')) {
            return;
        }

        Schema::table('main_meter_readings', function (Blueprint $table) {
            if (Schema::hasColumn('main_meter_readings', 'received_at')) {
                $table->dropColumn('received_at');
            }

            if (Schema::hasColumn('main_meter_readings', 'device_id')) {
                $table->dropIndex(['device_id']);
                $table->dropColumn('device_id');
            }

            if (Schema::hasColumn('main_meter_readings', 'input_source')) {
                $table->dropIndex(['input_source']);
                $table->dropColumn('input_source');
            }
        });
    }
};
