<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('energy_saving_recommendations', function (Blueprint $table) {
            $table->index(
                ['facility_id', 'year', 'month'],
                'energy_saving_recommendation_period_index'
            );
        });
        Schema::table('energy_saving_recommendations', function (Blueprint $table) {
            $table->dropUnique('energy_saving_recommendation_period_unique');
        });
    }

    public function down(): void
    {
        Schema::table('energy_saving_recommendations', function (Blueprint $table) {
            $table->unique(
                ['facility_id', 'year', 'month'],
                'energy_saving_recommendation_period_unique'
            );
        });
        Schema::table('energy_saving_recommendations', function (Blueprint $table) {
            $table->dropIndex('energy_saving_recommendation_period_index');
        });
    }
};
