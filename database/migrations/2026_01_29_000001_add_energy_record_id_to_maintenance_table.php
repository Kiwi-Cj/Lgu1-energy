<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('maintenance', function (Blueprint $table) {
            $table->unsignedBigInteger('energy_record_id')->nullable()->after('facility_id');
            $table->foreign('energy_record_id')->references('id')->on('energy_records')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('maintenance', function (Blueprint $table) {
            $table->dropForeign(['energy_record_id']);
            $table->dropColumn('energy_record_id');
        });
    }
};
