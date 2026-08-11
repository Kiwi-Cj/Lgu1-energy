<?php

use App\Models\Facility;
use App\Services\CprfFacilitySyncService;
use Illuminate\Support\Facades\Http;

function fakeCprfFacilityFeed(?string $imageUrl): void
{
    Http::fake([
        'https://cprf.test/facilities-feed' => Http::response([
            'success' => true,
            'data' => [[
                'id' => 42,
                'name' => 'Culiat Covered Court',
                'location' => 'Culiat, Quezon City',
                'barangay' => 'Culiat',
                'operating_hours' => '08:00-21:00',
                'status' => 'available',
                'image_url' => $imageUrl,
            ]],
        ]),
    ]);
}

beforeEach(function () {
    config([
        'services.cprf_integration.facilities_feed_url' => 'https://cprf.test/facilities-feed',
        'services.cprf_integration.token' => 'shared-token',
    ]);
});

test('it syncs the CPRF facility photo URL', function () {
    fakeCprfFacilityFeed('https://cprf.test/uploads/facilities/covered-court.jpg');

    $result = app(CprfFacilitySyncService::class)->sync();
    $facility = Facility::where('source', 'cprf')->where('external_ref', 42)->firstOrFail();

    expect($result['success'])->toBeTrue()
        ->and($facility->image_path)->toBe('https://cprf.test/uploads/facilities/covered-court.jpg')
        ->and($facility->resolved_image_url)->toBe('https://cprf.test/uploads/facilities/covered-court.jpg');
});

test('it keeps a local photo while CPRF has no photo', function () {
    Facility::create([
        'name' => 'Old name',
        'type' => 'Public Facility',
        'status' => 'active',
        'source' => 'cprf',
        'external_ref' => 42,
        'image_path' => 'uploads/facility_images/local.jpg',
    ]);
    fakeCprfFacilityFeed(null);

    app(CprfFacilitySyncService::class)->sync();

    expect(Facility::where('external_ref', 42)->value('image_path'))
        ->toBe('uploads/facility_images/local.jpg');
});

test('it clears a removed CPRF photo URL', function () {
    Facility::create([
        'name' => 'Old name',
        'type' => 'Public Facility',
        'status' => 'active',
        'source' => 'cprf',
        'external_ref' => 42,
        'image_path' => 'https://cprf.test/uploads/facilities/old.jpg',
    ]);
    fakeCprfFacilityFeed(null);

    app(CprfFacilitySyncService::class)->sync();

    expect(Facility::where('external_ref', 42)->value('image_path'))->toBeNull();
});
