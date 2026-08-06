<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Services\UmanMonthlyRecordSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('it imports CPRF records from UMAN idempotently into the matching mirrored facility', function () {
    config([
        'services.uman_monthly_records.url' => 'http://uman.test/api/monthly-energy-records.php',
        'services.uman_monthly_records.key' => 'shared-key',
    ]);

    $facility = Facility::query()->create([
        'name' => 'CPRF City Hall Annex',
        'type' => 'Public Facility',
        'status' => 'active',
        'source' => 'cprf',
        'external_ref' => 42,
    ]);

    $consumption = 1250.5;
    Http::fake(function () use (&$consumption) {
        return Http::response([
            'success' => true,
            'data' => [[
                'source_record_id' => 'ENG-202608-0001',
                'facility_key' => 'cprf:42',
                'cprf_facility_id' => 42,
                'facility_name' => 'City Hall Annex',
                'facility_type' => 'Public Facility',
                'location' => 'Quezon City',
                'year' => 2026,
                'month' => 8,
                'consumption_kwh' => $consumption,
                'cost' => 15006,
                'rate_per_kwh' => 12,
                'recorded_at' => '2026-08-06 10:00:00',
                'notes' => null,
            ]],
            'meta' => ['page' => 1, 'per_page' => 200, 'total' => 1, 'last_page' => 1],
        ]);
    });

    $first = app(UmanMonthlyRecordSyncService::class)->sync();

    expect($first['success'])->toBeTrue()
        ->and($first['created'])->toBe(1)
        ->and($first['updated'])->toBe(0)
        ->and(Cache::get('integrations.uman_monthly_records')['state'] ?? null)->toBe('connected');

    $meter = FacilityMeter::where('facility_id', $facility->id)->firstOrFail();
    $record = EnergyRecord::where('external_source', 'uman_cprf')
        ->where('external_record_id', 'ENG-202608-0001')
        ->firstOrFail();

    expect($meter->meter_type)->toBe('main')
        ->and($meter->approved_at)->not->toBeNull()
        ->and($record->facility_id)->toBe($facility->id)
        ->and($record->meter_id)->toBe($meter->id)
        ->and((float) $record->actual_kwh)->toBe(1250.5)
        ->and($record->input_source)->toBe('cprf')
        ->and($record->recorded_by_name)->toBe('CPRF via UMAN')
        ->and($record->review_status)->toBe('approved');

    $consumption = 1300.0;
    $second = app(UmanMonthlyRecordSyncService::class)->sync();

    expect($second['created'])->toBe(0)
        ->and($second['updated'])->toBe(1)
        ->and(EnergyRecord::where('external_source', 'uman_cprf')->count())->toBe(1)
        ->and((float) $record->fresh()->actual_kwh)->toBe(1300.0);

    Http::assertSent(fn (Request $request) => $request->hasHeader('X-API-Key', 'shared-key'));
});

test('it never sends a CPRF record to local manual facility 27', function () {
    config([
        'services.uman_monthly_records.url' => 'https://uman.infragovservices.com/api/monthly-energy-records.php',
        'services.uman_monthly_records.key' => 'shared-key',
    ]);

    Facility::query()->forceCreate([
        'id' => 27,
        'name' => 'LGU Health Office',
        'type' => 'Public Facility',
        'status' => 'active',
        'source' => 'local',
    ]);
    $cprfFacility = Facility::query()->create([
        'name' => 'CPRF Public Facility',
        'type' => 'Public Facility',
        'status' => 'active',
        'source' => 'cprf',
        'external_ref' => 88,
    ]);

    Http::fake([
        '*' => Http::response([
            'success' => true,
            'data' => [[
                'source_record_id' => 'ENG-202608-0027',
                'facility_key' => 'cprf:88',
                'cprf_facility_id' => 88,
                'facility_name' => 'CPRF Public Facility',
                'facility_type' => 'Public Facility',
                'location' => 'Quezon City',
                'year' => 2026,
                'month' => 8,
                'consumption_kwh' => 2200,
                'cost' => 26400,
                'rate_per_kwh' => 12,
                'recorded_at' => '2026-08-06 10:00:00',
            ]],
            'meta' => ['last_page' => 1],
        ]),
    ]);

    $result = app(UmanMonthlyRecordSyncService::class)->sync();

    expect($result['success'])->toBeTrue()
        ->and($result['created'])->toBe(1)
        ->and(EnergyRecord::where('external_record_id', 'ENG-202608-0027')->value('facility_id'))->toBe($cprfFacility->id)
        ->and(EnergyRecord::where('facility_id', 27)->count())->toBe(0)
        ->and(FacilityMeter::where('facility_id', $cprfFacility->id)->value('meter_name'))->toBe('CPRF Integrated Main Meter');
});

test('the UMAN sync command reports missing configuration without importing', function () {
    config([
        'services.uman_monthly_records.url' => null,
        'services.uman_monthly_records.key' => null,
    ]);

    $this->artisan('energy:sync-uman-monthly-records')
        ->expectsOutputToContain('Sync failed')
        ->assertFailed();

    expect(Cache::get('integrations.uman_monthly_records')['state'] ?? null)->toBe('not_configured');
});
