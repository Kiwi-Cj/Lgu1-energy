<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('maintenance')) {
            $hasIssueType = Schema::hasColumn('maintenance', 'issue_type');
            $hasTriggerMonth = Schema::hasColumn('maintenance', 'trigger_month');
            $hasMaintenanceType = Schema::hasColumn('maintenance', 'maintenance_type');
            $hasScheduledDate = Schema::hasColumn('maintenance', 'scheduled_date');
            $hasAssignedTo = Schema::hasColumn('maintenance', 'assigned_to');
            $hasRemarks = Schema::hasColumn('maintenance', 'remarks');

            if (! $hasIssueType || ! $hasTriggerMonth || ! $hasMaintenanceType || ! $hasScheduledDate || ! $hasAssignedTo || ! $hasRemarks) {
                Schema::table('maintenance', function (Blueprint $table) use (
                    $hasIssueType,
                    $hasTriggerMonth,
                    $hasMaintenanceType,
                    $hasScheduledDate,
                    $hasAssignedTo,
                    $hasRemarks
                ) {
                    if (! $hasIssueType) {
                        $table->string('issue_type')->nullable();
                    }

                    if (! $hasTriggerMonth) {
                        $table->string('trigger_month')->nullable();
                    }

                    if (! $hasMaintenanceType) {
                        $table->string('maintenance_type')->default('Preventive');
                    }

                    if (! $hasScheduledDate) {
                        $table->date('scheduled_date')->nullable();
                    }

                    if (! $hasAssignedTo) {
                        $table->string('assigned_to')->nullable();
                    }

                    if (! $hasRemarks) {
                        $table->text('remarks')->nullable();
                    }
                });
            }
        }

        if (! Schema::hasTable('maintenance_history')) {
            Schema::create('maintenance_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
                $table->string('issue_type')->nullable();
                $table->string('trigger_month')->nullable();
                $table->string('efficiency_rating')->nullable();
                $table->string('trend')->nullable();
                $table->string('maintenance_type')->default('Preventive');
                $table->string('maintenance_status')->default('Completed');
                $table->date('scheduled_date')->nullable();
                $table->string('assigned_to')->nullable();
                $table->date('completed_date')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('maintenance_history')) {
            Schema::drop('maintenance_history');
        }

        if (! Schema::hasTable('maintenance')) {
            return;
        }

        Schema::table('maintenance', function (Blueprint $table) {
            foreach (['issue_type', 'trigger_month', 'maintenance_type', 'scheduled_date', 'assigned_to', 'remarks'] as $column) {
                if (Schema::hasColumn('maintenance', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
