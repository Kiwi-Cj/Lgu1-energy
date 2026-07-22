<?php

namespace Database\Seeders;

use App\Models\ConservationGoal;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleConservationGoalSeeder extends Seeder
{
    public function run(): void
    {
        $facility = Facility::query()
            ->where('name', 'LGU City Hall Main Building')
            ->first() ?? Facility::query()->first();

        if (! $facility) {
            return;
        }

        $monthlyTotals = EnergyRecord::query()
            ->where('facility_id', $facility->id)
            ->whereHas('meter', fn ($query) => $query->where('meter_type', 'main'))
            ->get(['year', 'month', 'actual_kwh'])
            ->groupBy(fn (EnergyRecord $record) => $record->year.'-'.$record->month)
            ->map(fn ($records) => (float) $records->sum('actual_kwh'));

        $baseline = round((float) ($monthlyTotals->avg() ?: $facility->baseline_kwh ?: 10000), 2);

        ConservationGoal::updateOrCreate(
            ['name' => 'Reduce City Hall Monthly Electricity Consumption'],
            [
                'facility_id' => $facility->id,
                'description' => 'Reduce monthly electricity use through improved lighting, air-conditioning, and equipment schedules.',
                'goal_type' => 'monthly',
                'target_metric' => 'reduction_percent',
                'target_value' => 15,
                'baseline_value' => $baseline,
                'baseline_start_date' => '2026-01-01',
                'baseline_end_date' => '2026-06-30',
                'responsible_department' => 'Engineering Department',
                'action_plan' => "Maintain AC temperature at 24–26°C.\nTurn off lights and equipment in unoccupied areas.\nReview high-energy equipment every week.",
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-31',
                'status' => 'active',
                'created_by' => User::query()->whereIn('role', ['super_admin', 'admin', 'energy_officer', 'engineer'])->value('id'),
            ]
        );
    }
}
