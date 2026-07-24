<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\MainMeterReading;
use App\Models\Submeter;
use App\Models\SubmeterReading;
use App\Models\User;
use App\Services\ArchivePruneService;
use App\Services\MainMeterBaselineAlertService;
use App\Services\SubmeterBaselineAlertService;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

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

Artisan::command('archive:prune-expired
    {--days=30 : Permanently delete archive rows older than this many days}
    {--dry-run : Preview how many archived rows would be permanently deleted}', function () {
    $days = max(1, (int) $this->option('days'));
    $dryRun = (bool) $this->option('dry-run');

    $result = app(ArchivePruneService::class)->pruneExpired($days, $dryRun);
    $cutoff = $result['cutoff']->format('Y-m-d H:i:s');

    $this->info(($dryRun ? 'Preview' : 'Pruned') . " archives older than {$days} day(s).");
    $this->line("Cutoff: {$cutoff}");
    $this->line("Facilities: {$result['facilities']}");
    $this->line("Meters: {$result['meters']}");
    $this->line("Monthly records: {$result['monthly_records']}");
})->purpose('Permanently delete archived facilities, meters, and monthly records after the retention window');

Schedule::command('archive:prune-expired --days=30')
    ->dailyAt('01:30')
    ->withoutOverlapping();

// Mirror CPRF (facilities reservation) public facilities hourly. Manual
// "Sync now" button on the Facilities page's Public Facilities tab runs
// the same service on demand.
Schedule::command('energy:sync-cprf-facilities')
    ->hourly()
    ->withoutOverlapping();

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

Artisan::command('demo:seed-fake-sensors {--submeters=6 : Number of active submeters to seed}', function () {
    $mainService = app(MainMeterBaselineAlertService::class);
    $submeterService = app(SubmeterBaselineAlertService::class);

    $mainMeters = FacilityMeter::query()
        ->where('meter_type', 'main')
        ->orderBy('facility_id')
        ->limit(4)
        ->get(['id', 'facility_id', 'meter_name', 'baseline_kwh']);

    if ($mainMeters->isEmpty()) {
        $facility = Facility::query()->orderBy('id')->first(['id', 'name']);
        if ($facility) {
            $meter = FacilityMeter::query()->create([
                'facility_id' => (int) $facility->id,
                'meter_name' => 'Fake Main Sensor Meter',
                'meter_number' => 'FAKE-MAIN-001',
                'meter_type' => 'main',
                'status' => 'active',
                'multiplier' => 1,
                'baseline_kwh' => is_numeric($facility->baseline_kwh) ? (float) $facility->baseline_kwh : 1000,
                'notes' => 'Demo fake sensor main meter',
                'approved_at' => now(),
            ]);

            $mainMeters = collect([$meter]);
        }
    }

    $mainCreated = 0;
    foreach ($mainMeters as $index => $meter) {
        $baseStart = 10000 + ($index * 1750);

        for ($i = 0; $i < 12; $i++) {
            $month = now()->copy()->subMonthsNoOverflow(11 - $i)->startOfMonth();
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();
            $usage = 720 + ($index * 85) + ($i * 18) + (($i % 3) * 45);
            $readingStart = $baseStart + ($i * 920);
            $readingEnd = $readingStart + $usage;

            $reading = MainMeterReading::updateOrCreate(
                [
                    'facility_id' => (int) $meter->facility_id,
                    'period_type' => 'monthly',
                    'period_start_date' => $start->toDateString(),
                    'period_end_date' => $end->toDateString(),
                ],
                [
                    'reading_start_kwh' => round($readingStart, 2),
                    'reading_end_kwh' => round($readingEnd, 2),
                    'operating_days' => $start->daysInMonth,
                    'peak_demand_kw' => round($usage / max(1, $start->daysInMonth * 24) / 0.60, 2),
                    'power_factor' => 0.95,
                    'input_source' => 'iot',
                    'device_id' => 'FAKE-MAIN-' . str_pad((string) $meter->id, 4, '0', STR_PAD_LEFT),
                    'received_at' => $end->copy()->setTime(8 + ($index % 6), 15, 0),
                    'approved_at' => now(),
                ]
            );

            $mainService->processReading($reading->fresh(['facility']));
            $mainCreated++;
        }
    }

    $limit = max(1, (int) $this->option('submeters'));
    $submeters = Submeter::query()
        ->whereHas('facility')
        ->orderBy('facility_id')
        ->orderBy('submeter_name')
        ->limit($limit)
        ->get(['id', 'facility_id', 'submeter_name']);

    if ($submeters->isEmpty()) {
        $facility = Facility::query()->orderBy('id')->first(['id', 'name']);
        if ($facility) {
            $created = collect();
            foreach (['Lighting Sensor', 'Outlet Sensor', 'Aircon Sensor'] as $name) {
                $created->push(Submeter::query()->updateOrCreate(
                    [
                        'facility_id' => (int) $facility->id,
                        'submeter_name' => $name,
                    ],
                    [
                        'meter_type' => 'single_phase',
                        'status' => 'active',
                    ]
                ));
            }

            $submeters = $created->take($limit)->values();
        }
    }

    $subCreated = 0;
    foreach ($submeters as $index => $submeter) {
        $deviceId = 'FAKE-SUB-' . str_pad((string) $submeter->id, 4, '0', STR_PAD_LEFT);

        for ($i = 0; $i < 30; $i++) {
            $day = now()->copy()->subDays(29 - $i)->startOfDay();
            $usage = 18 + ($index * 3) + (($i % 7) * 1.7);
            $readingStart = 1500 + ($index * 420) + ($i * 31);

            $reading = SubmeterReading::updateOrCreate(
                [
                    'submeter_id' => (int) $submeter->id,
                    'period_type' => 'daily',
                    'period_start_date' => $day->toDateString(),
                    'period_end_date' => $day->toDateString(),
                ],
                [
                    'reading_start_kwh' => round($readingStart, 2),
                    'reading_end_kwh' => round($readingStart + $usage, 2),
                    'operating_days' => 1,
                    'input_source' => 'iot',
                    'device_id' => $deviceId,
                    'received_at' => $day->copy()->setTime(7 + ($index % 5), 30, 0),
                    'approved_at' => now(),
                ]
            );

            $submeterService->processReading($reading->fresh(['submeter.facility']));
            $subCreated++;
        }

        for ($i = 0; $i < 12; $i++) {
            $week = now()->copy()->subWeeks(11 - $i)->startOfWeek();
            $start = $week->copy()->startOfWeek();
            $end = $week->copy()->endOfWeek();
            $usage = 140 + ($index * 16) + (($i % 4) * 14);
            $readingStart = 4200 + ($index * 900) + ($i * 165);

            $reading = SubmeterReading::updateOrCreate(
                [
                    'submeter_id' => (int) $submeter->id,
                    'period_type' => 'weekly',
                    'period_start_date' => $start->toDateString(),
                    'period_end_date' => $end->toDateString(),
                ],
                [
                    'reading_start_kwh' => round($readingStart, 2),
                    'reading_end_kwh' => round($readingStart + $usage, 2),
                    'operating_days' => 7,
                    'input_source' => 'iot',
                    'device_id' => $deviceId,
                    'received_at' => $end->copy()->setTime(8 + ($index % 5), 0, 0),
                    'approved_at' => now(),
                ]
            );

            $submeterService->processReading($reading->fresh(['submeter.facility']));
            $subCreated++;
        }

        for ($i = 0; $i < 24; $i++) {
            $month = now()->copy()->subMonthsNoOverflow(23 - $i)->startOfMonth();
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();
            $usage = 520 + ($index * 55) + (($i % 6) * 22);
            $readingStart = 9000 + ($index * 1200) + ($i * 610);

            $reading = SubmeterReading::updateOrCreate(
                [
                    'submeter_id' => (int) $submeter->id,
                    'period_type' => 'monthly',
                    'period_start_date' => $start->toDateString(),
                    'period_end_date' => $end->toDateString(),
                ],
                [
                    'reading_start_kwh' => round($readingStart, 2),
                    'reading_end_kwh' => round($readingStart + $usage, 2),
                    'operating_days' => $start->daysInMonth,
                    'input_source' => 'iot',
                    'device_id' => $deviceId,
                    'received_at' => $end->copy()->setTime(9 + ($index % 5), 0, 0),
                    'approved_at' => now(),
                ]
            );

            $submeterService->processReading($reading->fresh(['submeter.facility']));
            $subCreated++;
        }
    }

    $this->info("Seeded fake sensor data. Main rows touched: {$mainCreated}. Submeter rows touched: {$subCreated}.");
})->purpose('Seed fake IoT sensor readings for main meter and submeter graphs');
