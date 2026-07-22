<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $keys = [
        'open_lights', 'open_aircon', 'open_equipment',
        'close_lights', 'close_aircon', 'close_equipment',
    ];

    public function up(): void
    {
        if (Schema::hasTable('daily_energy_checklist_tasks')) {
            DB::table('daily_energy_checklist_tasks')
                ->whereIn('task_key', $this->keys)
                ->whereNull('facility_id')
                ->update(['is_active' => false, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('daily_energy_checklist_tasks')) {
            DB::table('daily_energy_checklist_tasks')
                ->whereIn('task_key', $this->keys)
                ->whereNull('facility_id')
                ->update(['is_active' => true, 'updated_at' => now()]);
        }
    }
};
