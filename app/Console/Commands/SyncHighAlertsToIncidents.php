<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EnergyRecord;
use App\Models\EnergyIncident;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncHighAlertsToIncidents extends Command
{
    protected $signature = 'energy:sync-high-alerts';
    protected $description = 'Sync all high alert monthly records to the energy_incidents table';

    public function handle()
    {
        $records = EnergyRecord::with('facility')->get();
        $count = 0;
        foreach ($records as $record) {
            $facility = $record->facility;
            if (!$facility) continue;
            $baseline = $facility->baseline_kwh ?? 0;
            $actual = $record->actual_kwh;
            if ($baseline <= 0) continue;
            $deviation = round((($actual - $baseline) / $baseline) * 100, 2);
            $sizeLabel = '';
            if ($baseline <= 1000) {
                $sizeLabel = 'Small';
            } elseif ($baseline <= 3000) {
                $sizeLabel = 'Medium';
            } elseif ($baseline <= 10000) {
                $sizeLabel = 'Large';
            } else {
                $sizeLabel = 'Extra Large';
            }
            $isHighAlert =
                ($sizeLabel === 'Small' && $deviation > 30) ||
                ($sizeLabel === 'Medium' && $deviation > 20) ||
                (($sizeLabel === 'Large' || $sizeLabel === 'Extra Large') && $deviation > 15);
            if ($isHighAlert) {
                // Avoid duplicate for same facility, month, year
                $exists = EnergyIncident::where('facility_id', $record->facility_id)
                    ->whereYear('date_detected', $record->year)
                    ->whereMonth('date_detected', $record->month)
                    ->where('description', 'High Alert')
                    ->first();
                if (!$exists) {
                    EnergyIncident::create([
                        'facility_id' => $record->facility_id,
                        'description' => 'High Alert',
                        'status' => 'High Alert',
                        'date_detected' => $record->created_at ?? now(),
                        'created_by' => $record->recorded_by ?? null,
                    ]);
                    $count++;
                }
            }
        }
        $this->info("$count high alert incidents synced.");
    }
}
