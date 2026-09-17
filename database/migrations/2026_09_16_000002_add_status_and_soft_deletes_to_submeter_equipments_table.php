<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('submeter_equipments')) {
            return;
        }

        Schema::table('submeter_equipments', function (Blueprint $table) {
            if (! Schema::hasColumn('submeter_equipments', 'status')) {
                $table->string('status', 32)->default('active')->after('category');
                $table->index(['facility_id', 'status'], 'submeter_equipments_facility_status_idx');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('submeter_equipments')) {
            return;
        }

        Schema::table('submeter_equipments', function (Blueprint $table) {
            if (Schema::hasColumn('submeter_equipments', 'status')) {
                $table->dropIndex('submeter_equipments_facility_status_idx');
                $table->dropColumn('status');
            }
        });
    }
};
