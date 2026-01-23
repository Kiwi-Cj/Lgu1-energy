<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('energy_efficiency', function (Blueprint $table) {
            $table->unsignedBigInteger('facility_id')->nullable()->after('id');
        });
        // Optional: migrate data from 'facility' string to 'facility_id' if possible
    }

    public function down(): void
    {
        Schema::table('energy_efficiency', function (Blueprint $table) {
            $table->dropColumn('facility_id');
        });
    }
};
