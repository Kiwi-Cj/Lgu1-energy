<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('energy_incidents') || ! Schema::hasColumn('energy_incidents', 'status')) {
            return;
        }

        DB::table('energy_incidents')
            ->whereRaw('LOWER(status) LIKE ?', ['%pending%'])
            ->update(['status' => 'Open']);
    }

    public function down(): void
    {
        // "Pending" and "Open" represented the same active state, so the
        // normalization is intentionally not reversed.
    }
};
