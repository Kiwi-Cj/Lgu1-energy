<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('facilities')) {
            return;
        }

        if (! Schema::hasColumn('facilities', 'floor_area_sqm')) {
            Schema::table('facilities', function (Blueprint $table) {
                $table->decimal('floor_area_sqm', 12, 2)->nullable()->after('floor_area');
            });
        }

        if (Schema::hasColumn('facilities', 'floor_area') && Schema::hasColumn('facilities', 'floor_area_sqm')) {
            DB::table('facilities')
                ->whereNull('floor_area_sqm')
                ->whereNotNull('floor_area')
                ->update([
                    'floor_area_sqm' => DB::raw('floor_area'),
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('facilities') && Schema::hasColumn('facilities', 'floor_area_sqm')) {
            Schema::table('facilities', function (Blueprint $table) {
                $table->dropColumn('floor_area_sqm');
            });
        }
    }
};

