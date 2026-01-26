<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('baseline_status')->default('collecting');
            $table->date('baseline_start_date')->nullable();
            $table->decimal('baseline_kwh', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn(['baseline_status', 'baseline_start_date', 'baseline_kwh']);
        });
    }
};
