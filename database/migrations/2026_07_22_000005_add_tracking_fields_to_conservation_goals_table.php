<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('conservation_goals', function (Blueprint $table) {
            $table->decimal('baseline_value', 14, 2)->nullable()->after('target_value');
            $table->date('baseline_start_date')->nullable()->after('baseline_value');
            $table->date('baseline_end_date')->nullable()->after('baseline_start_date');
            $table->string('responsible_department')->nullable()->after('baseline_end_date');
            $table->text('action_plan')->nullable()->after('responsible_department');
        });
    }

    public function down(): void
    {
        Schema::table('conservation_goals', function (Blueprint $table) {
            $table->dropColumn(['baseline_value', 'baseline_start_date', 'baseline_end_date', 'responsible_department', 'action_plan']);
        });
    }
};
