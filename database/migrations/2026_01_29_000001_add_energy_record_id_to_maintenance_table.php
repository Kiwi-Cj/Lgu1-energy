<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('maintenance')) {
            return;
        }

        Schema::table('maintenance', function (Blueprint $table) {
            if (!Schema::hasColumn('maintenance', 'energy_record_id')) {
                $table->unsignedBigInteger('energy_record_id')->nullable()->after('facility_id');
                $table->foreign('energy_record_id')->references('id')->on('energy_records')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('maintenance')) {
            return;
        }

        Schema::table('maintenance', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance', 'energy_record_id')) {
                try {
                    $table->dropForeign(['energy_record_id']);
                } catch (\Throwable $e) {
                    // Ignore if foreign key does not exist.
                }
                $table->dropColumn('energy_record_id');
            }
        });
    }
};
