<?php

use App\Models\EnergyProfile;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use Database\Seeders\QcLguFacilityDatasetSeeder;

test('QC LGU facility dataset is complete and idempotent', function () {
    $this->seed(QcLguFacilityDatasetSeeder::class);
    $this->seed(QcLguFacilityDatasetSeeder::class);

    $facilities = Facility::where('source_key', 'like', 'qc-lgu-dataset:%')->get();
    $facilityIds = $facilities->pluck('id');

    expect($facilities)->toHaveCount(8)
        ->and($facilities->every(fn (Facility $facility) => $facility->source === 'local'))->toBeTrue()
        ->and($facilities->every(fn (Facility $facility) =>
            $facility->floor_area === null
            && $facility->floor_area_sqm === null
            && $facility->floors === null
            && $facility->year_built === null
        ))->toBeTrue()
        ->and(FacilityMeter::whereIn('facility_id', $facilityIds)->where('meter_type', 'main')->count())->toBe(8)
        ->and(EnergyProfile::whereIn('facility_id', $facilityIds)->count())->toBe(8)
        ->and(EnergyRecord::whereIn('facility_id', $facilityIds)->where('review_status', 'approved')->count())->toBe(56)
        ->and(EnergyRecord::whereIn('facility_id', $facilityIds)->where('year', 2026)->where('month', 8)->count())->toBe(0);
});
