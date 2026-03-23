<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('energy_incidents')) {
            return;
        }

        Schema::table('energy_incidents', function (Blueprint $table) {
            if (! Schema::hasColumn('energy_incidents', 'description')) {
                $table->text('description')->nullable()->after('message');
            }

            if (! Schema::hasColumn('energy_incidents', 'date_detected')) {
                $table->date('date_detected')->nullable()->after('status');
            }

            if (! Schema::hasColumn('energy_incidents', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('date_detected');
            }

            if (! Schema::hasColumn('energy_incidents', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('created_by');
            }
        });

        if (Schema::hasColumn('energy_incidents', 'message') && Schema::hasColumn('energy_incidents', 'description')) {
            DB::table('energy_incidents')
                ->whereNull('description')
                ->update([
                    'description' => DB::raw('message'),
                ]);
        }

        if (Schema::hasColumn('energy_incidents', 'date_detected')) {
            DB::table('energy_incidents')
                ->whereNull('date_detected')
                ->update([
                    'date_detected' => DB::raw('DATE(created_at)'),
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('energy_incidents')) {
            return;
        }

        Schema::table('energy_incidents', function (Blueprint $table) {
            if (Schema::hasColumn('energy_incidents', 'resolved_at')) {
                $table->dropColumn('resolved_at');
            }

            if (Schema::hasColumn('energy_incidents', 'created_by')) {
                $table->dropColumn('created_by');
            }

            if (Schema::hasColumn('energy_incidents', 'date_detected')) {
                $table->dropColumn('date_detected');
            }

            if (Schema::hasColumn('energy_incidents', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
