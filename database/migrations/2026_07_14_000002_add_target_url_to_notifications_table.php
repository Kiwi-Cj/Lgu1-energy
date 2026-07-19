<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'target_url')) {
                $table->string('target_url')->nullable()->after('type');
            }
        });

        DB::table('notifications')
            ->whereNull('target_url')
            ->orderBy('id')
            ->chunkById(200, function ($notifications) {
                foreach ($notifications as $notification) {
                    $message = strtolower((string) ($notification->message ?? ''));
                    $title = strtolower((string) ($notification->title ?? ''));
                    $type = strtolower((string) ($notification->type ?? ''));

                    $targetUrl = null;

                    if (str_contains($message, 'checklist') || str_contains($title, 'checklist')) {
                        $targetUrl = route('modules.energy-conservation.feature', [
                            'feature' => 'daily-checklist',
                            'month' => now()->format('Y-m'),
                        ]);
                    } elseif ($type === 'maintenance' || str_contains($message, 'maintenance:')) {
                        $targetUrl = str_contains($message, 'completed')
                            ? route('maintenance.history')
                            : route('modules.maintenance.index');
                    } elseif ($type === 'incident' || str_contains($message, 'incident:')) {
                        $targetUrl = route('energy-incidents.index');
                    } elseif ($type === 'contact' || str_contains($message, 'contact message')) {
                        $targetUrl = route('modules.contact-messages.index');
                    } else {
                        $targetUrl = route('dashboard.index');
                    }

                    DB::table('notifications')
                        ->where('id', $notification->id)
                        ->update(['target_url' => $targetUrl]);
                }
            }, 'id');
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'target_url')) {
                $table->dropColumn('target_url');
            }
        });
    }
};
