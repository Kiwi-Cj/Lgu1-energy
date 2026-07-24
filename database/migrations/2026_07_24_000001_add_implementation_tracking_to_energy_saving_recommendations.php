<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('energy_saving_recommendations', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('target_date')->constrained('users')->nullOnDelete();
            $table->string('implementation_status', 20)->default('pending')->after('assigned_to');
            $table->decimal('actual_savings_kwh', 14, 2)->nullable()->after('implementation_status');
            $table->text('implementation_notes')->nullable()->after('actual_savings_kwh');
            $table->timestamp('implemented_at')->nullable()->after('implementation_notes');
            $table->foreignId('verified_by')->nullable()->after('implemented_at')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->index(['implementation_status', 'target_date'], 'energy_recommendation_implementation_index');
        });
    }

    public function down(): void
    {
        Schema::table('energy_saving_recommendations', function (Blueprint $table) {
            $table->dropIndex('energy_recommendation_implementation_index');
            $table->dropConstrainedForeignId('verified_by');
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropColumn([
                'implementation_status',
                'actual_savings_kwh',
                'implementation_notes',
                'implemented_at',
                'verified_at',
            ]);
        });
    }
};
