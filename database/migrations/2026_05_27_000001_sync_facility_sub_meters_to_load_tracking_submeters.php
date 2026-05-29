<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('facility_meters') || ! Schema::hasTable('submeters')) {
            return;
        }

        DB::table('facility_meters')
            ->where('meter_type', 'sub')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get(['facility_id', 'meter_name', 'status'])
            ->each(function ($meter) {
                $name = trim((string) ($meter->meter_name ?? ''));
                if ($name === '') {
                    return;
                }

                $attributes = [
                    'meter_type' => 'single_phase',
                    'status' => strtolower((string) ($meter->status ?? 'active')) === 'active' ? 'active' : 'inactive',
                    'updated_at' => now(),
                ];

                $existingId = DB::table('submeters')
                    ->where('facility_id', (int) $meter->facility_id)
                    ->where('submeter_name', $name)
                    ->value('id');

                if ($existingId) {
                    DB::table('submeters')->where('id', $existingId)->update($attributes);
                    return;
                }

                DB::table('submeters')->insert($attributes + [
                    'facility_id' => (int) $meter->facility_id,
                    'submeter_name' => $name,
                    'created_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        // Mirrors are intentionally left in place to avoid removing readings/equipment links.
    }
};
