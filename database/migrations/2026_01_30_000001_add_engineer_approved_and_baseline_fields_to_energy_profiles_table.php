<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('energy_profiles', function (Blueprint $table) {
            $table->boolean('engineer_approved')->default(false)->after('average_monthly_kwh');
            $table->boolean('baseline_locked')->default(false)->after('engineer_approved');
            $table->string('baseline_source')->nullable()->after('baseline_locked');
        });
    }

    public function down()
    {
        Schema::table('energy_profiles', function (Blueprint $table) {
            $table->dropColumn(['engineer_approved', 'baseline_locked', 'baseline_source']);
        });
    }
};
