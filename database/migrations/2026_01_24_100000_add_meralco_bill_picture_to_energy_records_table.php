<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (!Schema::hasColumn('energy_records', 'meralco_bill_picture')) {
                $table->string('meralco_bill_picture')->nullable()->after('alert_level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            if (Schema::hasColumn('energy_records', 'meralco_bill_picture')) {
                $table->dropColumn('meralco_bill_picture');
            }
        });
    }
};
