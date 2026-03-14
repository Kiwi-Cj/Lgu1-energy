<?php

use App\Models\EnergyRecord;
use App\Models\FacilityMeter;
use App\Models\MainMeterReading;
use App\Models\Submeter;
use App\Models\SubmeterReading;
use App\Models\User;
use App\Services\MainMeterBaselineAlertService;
use App\Services\SubmeterBaselineAlertService;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('demo:check-consumptions', function () {
    $rows = DB::table('consumptions')
        ->select('facility_id', 'month', 'kwh')
        ->orderBy('facility_id')
        ->orderBy('month')
        ->get();
    if ($rows->isEmpty()) {
        $this->info('No data found in consumptions table.');
        return;
    }
    $this->info("facility_id | month     | kwh");
    foreach ($rows as $row) {
        $this->line(sprintf("%11d | %s | %.2f", $row->facility_id, $row->month, $row->kwh));
    }
})->describe('Check consumptions table for demo data');

Artisan::command('energy:archive-legacy-facility-aggregate {--dry-run : Preview how many records will be archived}', function () {
    $query = EnergyRecord::query()
        ->whereNull('meter_id')
        ->whereNull('deleted_at');

    $count = (clone $query)->count();
    if ($count === 0) {
        $this->info('No active legacy facility-aggregate records found.');
        return;
    }

    if ((bool) $this->option('dry-run')) {
        $this->info("Legacy facility-aggregate records ready for archive: {$count}");
        return;
    }

    $archived = 0;
    $query->orderBy('id')->chunkById(200, function ($records) use (&$archived) {
        foreach ($records as $record) {
            $record->delete();
            $archived++;
        }
    });

    $this->info("Archived {$archived} legacy facility-aggregate record(s).");
})->purpose('Soft-delete old monthly energy records with null meter_id');

Artisan::command('main-meter:backfill-from-energy-records
    {--dry-run : Preview records to be migrated}
    {--approve : Mark migrated records as approved and recompute baseline/alerts}
    {--include-trashed : Include soft-deleted energy_records as source}', function () {
    $includeTrashed = (bool) $this->option('include-trashed');
    $approve = (bool) $this->option('approve');
    $dryRun = (bool) $this->option('dry-run');

    $baseQuery = EnergyRecord::query()
        ->whereNotNull('meter_id')
        ->whereNotNull('facility_id')
        ->whereNotNull('year')
        ->whereNotNull('month')
        ->whereNotNull('actual_kwh')
        ->whereHas('meter', fn ($meterQuery) => $meterQuery->where('meter_type', 'main'));

    if ($includeTrashed) {
        $baseQuery->withTrashed();
    }

    $source = $baseQuery
        ->orderBy('facility_id')
        ->orderBy('year')
        ->orderBy('month')
        ->orderByDesc('day')
        ->orderByDesc('id')
        ->get([
            'id',
            'facility_id',
            'year',
            'month',
            'day',
            'actual_kwh',
            'recorded_by',
            'created_at',
        ]);

    if ($source->isEmpty()) {
        $this->info('No source energy_records found for main meters.');
        return;
    }

    // Keep only the latest source row per facility + year + month.
    $candidates = $source
        ->groupBy(fn ($row) => $row->facility_id . '|' . (int) $row->year . '|' . (int) $row->month)
        ->map(fn ($group) => $group->first())
        ->values();

    $validUserIds = User::query()->pluck('id')->flip();
    $toInsert = collect();
    $invalidDateRows = 0;
    $invalidKwhRows = 0;

    foreach ($candidates as $row) {
        $year = (int) $row->year;
        $month = (int) $row->month;
        $kwh = is_numeric($row->actual_kwh) ? round((float) $row->actual_kwh, 2) : null;

        if ($year <= 0 || $month < 1 || $month > 12) {
            $invalidDateRows++;
            continue;
        }

        if ($kwh === null || $kwh < 0) {
            $invalidKwhRows++;
            continue;
        }

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $existing = MainMeterReading::query()
            ->where('facility_id', (int) $row->facility_id)
            ->where('period_type', 'monthly')
            ->whereDate('period_start_date', $start->toDateString())
            ->whereDate('period_end_date', $end->toDateString())
            ->exists();

        if ($existing) {
            continue;
        }

        $recordedBy = (int) ($row->recorded_by ?? 0);
        $encodedBy = $recordedBy > 0 && $validUserIds->has($recordedBy) ? $recordedBy : null;

        $toInsert->push([
            'facility_id' => (int) $row->facility_id,
            'period_type' => 'monthly',
            'period_start_date' => $start->toDateString(),
            'period_end_date' => $end->toDateString(),
            'reading_start_kwh' => 0,
            'reading_end_kwh' => $kwh,
            'operating_days' => null,
            'peak_demand_kw' => null,
            'power_factor' => null,
            'encoded_by' => $encodedBy,
            'approved_by' => $approve ? $encodedBy : null,
            'approved_at' => $approve ? ($row->created_at ?? now()) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $candidateCount = $candidates->count();
    $insertCount = $toInsert->count();
    $existingCount = $candidateCount - $insertCount - $invalidDateRows - $invalidKwhRows;

    $this->info("Source rows (latest per facility/month): {$candidateCount}");
    $this->line("Will insert: {$insertCount}");
    $this->line("Skipped existing: {$existingCount}");
    $this->line("Skipped invalid date: {$invalidDateRows}");
    $this->line("Skipped invalid kWh: {$invalidKwhRows}");

    if ($dryRun) {
        if ($insertCount > 0) {
            $preview = $toInsert->take(10)->map(function ($row) {
                return sprintf(
                    'facility=%d period=%s..%s kwh=%.2f approved=%s',
                    $row['facility_id'],
                    $row['period_start_date'],
                    $row['period_end_date'],
                    (float) $row['reading_end_kwh'],
                    $row['approved_at'] ? 'yes' : 'no'
                );
            });
            $this->newLine();
            $this->info('Preview (first 10):');
            foreach ($preview as $line) {
                $this->line(" - {$line}");
            }
        }
        return;
    }

    if ($insertCount > 0) {
        MainMeterReading::query()->insert($toInsert->all());
    }

    if ($approve && $insertCount > 0) {
        $service = app(MainMeterBaselineAlertService::class);
        $facilityIds = $toInsert->pluck('facility_id')->unique()->values();
        $recomputed = 0;

        MainMeterReading::query()
            ->approved()
            ->whereIn('facility_id', $facilityIds)
            ->where('period_type', 'monthly')
            ->orderBy('facility_id')
            ->orderBy('period_end_date')
            ->orderBy('id')
            ->chunk(200, function ($chunk) use ($service, &$recomputed) {
                foreach ($chunk as $reading) {
                    $service->processReading($reading);
                    $recomputed++;
                }
            });

        $this->line("Recomputed baseline/alerts for approved readings: {$recomputed}");
    }

    $this->info("Backfill complete. Inserted {$insertCount} main_meter_readings.");
})->purpose('Backfill main_meter_readings from old energy_records (main meters only)');

Artisan::command('submeter:sync-and-backfill-from-energy-records
    {--dry-run : Preview sync/backfill changes}
    {--approve : Mark migrated readings approved and recompute baseline/alerts}
    {--include-trashed : Include soft-deleted energy_records as source}', function () {
    $includeTrashed = (bool) $this->option('include-trashed');
    $approve = (bool) $this->option('approve');
    $dryRun = (bool) $this->option('dry-run');

    $validUserIds = User::query()->pluck('id')->flip();

    $facilitySubMeters = FacilityMeter::query()
        ->where('meter_type', 'sub')
        ->orderBy('facility_id')
        ->orderBy('id')
        ->get(['id', 'facility_id', 'meter_name', 'status']);

    $existingSubmeters = Submeter::query()
        ->get(['id', 'facility_id', 'submeter_name', 'status'])
        ->keyBy(fn ($row) => (int) $row->facility_id . '|' . strtolower(trim((string) $row->submeter_name)));

    $submetersToCreate = collect();
    $submetersToActivate = [];
    $meterIdToSubmeterId = [];

    foreach ($facilitySubMeters as $meter) {
        $name = trim((string) $meter->meter_name);
        if ($name === '') {
            continue;
        }

        $key = (int) $meter->facility_id . '|' . strtolower($name);
        $desiredStatus = strtolower((string) $meter->status) === 'inactive' ? 'inactive' : 'active';
        $existing = $existingSubmeters->get($key);

        if (! $existing) {
            $submetersToCreate->push([
                'facility_id' => (int) $meter->facility_id,
                'submeter_name' => $name,
                'meter_type' => 'single_phase',
                'status' => $desiredStatus,
                'created_at' => now(),
                'updated_at' => now(),
                '_map_meter_id' => (int) $meter->id,
                '_map_key' => $key,
            ]);
            continue;
        }

        if ((string) $existing->status !== $desiredStatus) {
            $submetersToActivate[] = [(int) $existing->id, $desiredStatus];
        }
        $meterIdToSubmeterId[(int) $meter->id] = (int) $existing->id;
    }

    // Persist missing submeters first (unless dry-run), then rebuild map.
    if (! $dryRun && $submetersToCreate->isNotEmpty()) {
        $insertRows = $submetersToCreate->map(function ($row) {
            unset($row['_map_meter_id'], $row['_map_key']);
            return $row;
        })->all();
        Submeter::query()->insert($insertRows);
    }

    $submeterLookup = Submeter::query()
        ->get(['id', 'facility_id', 'submeter_name', 'status'])
        ->keyBy(fn ($row) => (int) $row->facility_id . '|' . strtolower(trim((string) $row->submeter_name)));

    foreach ($facilitySubMeters as $meter) {
        $name = trim((string) $meter->meter_name);
        if ($name === '') {
            continue;
        }
        $key = (int) $meter->facility_id . '|' . strtolower($name);
        $sub = $submeterLookup->get($key);
        if ($sub) {
            $meterIdToSubmeterId[(int) $meter->id] = (int) $sub->id;
        }
    }

    if (! $dryRun && ! empty($submetersToActivate)) {
        foreach ($submetersToActivate as [$submeterId, $status]) {
            Submeter::query()->whereKey($submeterId)->update(['status' => $status, 'updated_at' => now()]);
        }
    }

    $sourceQuery = EnergyRecord::query()
        ->whereNotNull('meter_id')
        ->whereNotNull('facility_id')
        ->whereNotNull('year')
        ->whereNotNull('month')
        ->whereNotNull('actual_kwh')
        ->whereHas('meter', fn ($meterQuery) => $meterQuery->where('meter_type', 'sub'));

    if ($includeTrashed) {
        $sourceQuery->withTrashed();
    }

    $source = $sourceQuery
        ->orderBy('facility_id')
        ->orderBy('meter_id')
        ->orderBy('year')
        ->orderBy('month')
        ->orderByDesc('day')
        ->orderByDesc('id')
        ->get([
            'id',
            'facility_id',
            'meter_id',
            'year',
            'month',
            'day',
            'actual_kwh',
            'recorded_by',
            'created_at',
        ]);

    $candidates = $source
        ->groupBy(fn ($row) => (int) $row->meter_id . '|' . (int) $row->year . '|' . (int) $row->month)
        ->map(fn ($group) => $group->first())
        ->values();

    $toInsert = collect();
    $missingSubmeterMap = 0;
    $invalidDateRows = 0;
    $invalidKwhRows = 0;

    foreach ($candidates as $row) {
        $meterId = (int) ($row->meter_id ?? 0);
        $submeterId = $meterIdToSubmeterId[$meterId] ?? null;
        if (! $submeterId) {
            $missingSubmeterMap++;
            continue;
        }

        $year = (int) $row->year;
        $month = (int) $row->month;
        $kwh = is_numeric($row->actual_kwh) ? round((float) $row->actual_kwh, 2) : null;

        if ($year <= 0 || $month < 1 || $month > 12) {
            $invalidDateRows++;
            continue;
        }

        if ($kwh === null || $kwh < 0) {
            $invalidKwhRows++;
            continue;
        }

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $existing = SubmeterReading::query()
            ->where('submeter_id', (int) $submeterId)
            ->where('period_type', 'monthly')
            ->whereDate('period_start_date', $start->toDateString())
            ->whereDate('period_end_date', $end->toDateString())
            ->exists();

        if ($existing) {
            continue;
        }

        $recordedBy = (int) ($row->recorded_by ?? 0);
        $encodedBy = $recordedBy > 0 && $validUserIds->has($recordedBy) ? $recordedBy : null;

        $toInsert->push([
            'submeter_id' => (int) $submeterId,
            'period_type' => 'monthly',
            'period_start_date' => $start->toDateString(),
            'period_end_date' => $end->toDateString(),
            'reading_start_kwh' => 0,
            'reading_end_kwh' => $kwh,
            'operating_days' => null,
            'encoded_by_user_id' => $encodedBy,
            'approved_by_engineer_id' => $approve ? $encodedBy : null,
            'approved_at' => $approve ? ($row->created_at ?? now()) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $createdSubmeters = $submetersToCreate->count();
    $candidateCount = $candidates->count();
    $insertCount = $toInsert->count();
    $existingCount = $candidateCount - $insertCount - $missingSubmeterMap - $invalidDateRows - $invalidKwhRows;
    $statusSyncCount = count($submetersToActivate);

    $this->info("Submeters to create from facility_meters: {$createdSubmeters}");
    $this->line("Submeters with status sync: {$statusSyncCount}");
    $this->line("Source rows (latest per meter/month): {$candidateCount}");
    $this->line("Will insert readings: {$insertCount}");
    $this->line("Skipped existing readings: {$existingCount}");
    $this->line("Skipped missing submeter mapping: {$missingSubmeterMap}");
    $this->line("Skipped invalid date: {$invalidDateRows}");
    $this->line("Skipped invalid kWh: {$invalidKwhRows}");

    if ($dryRun) {
        if ($submetersToCreate->isNotEmpty()) {
            $this->newLine();
            $this->info('Submeter create preview (first 10):');
            foreach ($submetersToCreate->take(10) as $row) {
                $this->line(sprintf(
                    ' - facility=%d name=%s status=%s',
                    (int) $row['facility_id'],
                    (string) $row['submeter_name'],
                    (string) $row['status']
                ));
            }
        }

        if ($toInsert->isNotEmpty()) {
            $this->newLine();
            $this->info('Reading insert preview (first 10):');
            foreach ($toInsert->take(10) as $row) {
                $this->line(sprintf(
                    ' - submeter=%d period=%s..%s kwh=%.2f approved=%s',
                    (int) $row['submeter_id'],
                    (string) $row['period_start_date'],
                    (string) $row['period_end_date'],
                    (float) $row['reading_end_kwh'],
                    $row['approved_at'] ? 'yes' : 'no'
                ));
            }
        }

        return;
    }

    if ($insertCount > 0) {
        SubmeterReading::query()->insert($toInsert->all());
    }

    if ($approve && $insertCount > 0) {
        $service = app(SubmeterBaselineAlertService::class);
        $submeterIds = $toInsert->pluck('submeter_id')->unique()->values();
        $recomputed = 0;

        SubmeterReading::query()
            ->approved()
            ->whereIn('submeter_id', $submeterIds)
            ->where('period_type', 'monthly')
            ->orderBy('submeter_id')
            ->orderBy('period_end_date')
            ->orderBy('id')
            ->chunk(200, function ($chunk) use ($service, &$recomputed) {
                foreach ($chunk as $reading) {
                    $service->processReading($reading);
                    $recomputed++;
                }
            });

        $this->line("Recomputed submeter baseline/alerts for approved readings: {$recomputed}");
    }

    $this->info("Submeter sync/backfill complete. Inserted {$insertCount} submeter_readings.");
})->purpose('Sync submeters from facility_meters and backfill submeter_readings from old energy_records');
