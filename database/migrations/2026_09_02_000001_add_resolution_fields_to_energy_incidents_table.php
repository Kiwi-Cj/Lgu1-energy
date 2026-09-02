<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('energy_incidents')) {
            Schema::table('energy_incidents', function (Blueprint $table) {
                if (! Schema::hasColumn('energy_incidents', 'immediate_action')) {
                    $table->text('immediate_action')->nullable()->after('description');
                }
                if (! Schema::hasColumn('energy_incidents', 'resolution_summary')) {
                    $table->text('resolution_summary')->nullable()->after('immediate_action');
                }
                if (! Schema::hasColumn('energy_incidents', 'preventive_recommendation')) {
                    $table->text('preventive_recommendation')->nullable()->after('resolution_summary');
                }
                if (! Schema::hasColumn('energy_incidents', 'probable_cause')) {
                    $table->text('probable_cause')->nullable()->after('preventive_recommendation');
                }
                if (! Schema::hasColumn('energy_incidents', 'resolved_by')) {
                    $table->unsignedBigInteger('resolved_by')->nullable()->after('created_by');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('energy_incidents')) {
            Schema::table('energy_incidents', function (Blueprint $table) {
                $columns = ['immediate_action', 'resolution_summary', 'preventive_recommendation', 'probable_cause', 'resolved_by'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('energy_incidents', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
