<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EnergyRecord;
use App\Models\EnergyIncident;
use App\Support\BaselineResolver;
use App\Support\EnergyAlertRouting;

class SyncHighAlertsToIncidents extends Command
{
    protected $signature = 'energy:sync-high-alerts';
    protected $description = 'Backfill incident-owned Critical and Very High monthly energy alerts';

    public function handle()
    {
        $records = EnergyRecord::with(['facility.energyProfiles', 'meter'])->get();
        $count = 0;

        foreach ($records as $record) {
            $facility = $record->facility;
            if (! $facility || $this->isSubmeterRecord($record)) {
                continue;
            }

            $baseline = BaselineResolver::forRecord($record, $facility);
            $actual = is_numeric($record->actual_kwh) ? (float) $record->actual_kwh : null;
            $deviation = EnergyRecord::calculateDeviation($actual, $baseline);
            $alert = EnergyRecord::resolveAlertLevel($deviation, $baseline);
            if (! EnergyAlertRouting::requiresIncident($alert)) {
                continue;
            }

            $incident = EnergyIncident::firstOrCreate(
                [
                    'facility_id' => $record->facility_id,
                    'month' => (int) $record->month,
                    'year' => (int) $record->year,
                ],
                [
                    'energy_record_id' => $record->id,
                    'category' => 'energy_anomaly',
                    'source' => strtolower(trim((string) $record->input_source)) === 'cprf' ? 'cprf' : 'auto',
                    'deviation_percent' => $deviation,
                    'description' => "{$alert} energy deviation detected and queued for incident review.",
                    'status' => 'Open',
                    'date_detected' => $record->created_at?->toDateString() ?? now()->toDateString(),
                    'created_by' => $record->recorded_by ?? null,
                ]
            );

            if ($incident->wasRecentlyCreated) {
                $count++;
            }
        }

        $this->info("{$count} incident-owned energy alert(s) backfilled.");

        return self::SUCCESS;
    }

    private function isSubmeterRecord(EnergyRecord $record): bool
    {
        return strtolower((string) ($record->meter?->meter_type ?? '')) === 'sub';
    }
}
