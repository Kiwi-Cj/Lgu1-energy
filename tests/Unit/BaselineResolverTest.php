<?php

use App\Models\EnergyProfile;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Support\BaselineResolver;

test('record snapshot is the first baseline source', function () {
    $record = new EnergyRecord(['baseline_kwh' => 1250]);
    $record->setRelation('meter', new FacilityMeter(['baseline_kwh' => 900]));

    expect(BaselineResolver::forRecord($record))->toBe(1250.0);
});

test('meter baseline is used before profile and facility fallbacks', function () {
    $facility = new Facility(['baseline_kwh' => 700]);
    $facility->setRelation('energyProfiles', collect([
        new EnergyProfile(['baseline_kwh' => 800]),
    ]));

    $record = new EnergyRecord();
    $record->setRelation('meter', new FacilityMeter(['baseline_kwh' => 900]));

    expect(BaselineResolver::forRecord($record, $facility))->toBe(900.0);
});

test('facility-level readings retain profile then facility fallback compatibility', function () {
    $facility = new Facility(['baseline_kwh' => 700]);
    $facility->setRelation('energyProfiles', collect([
        new EnergyProfile(['id' => 1, 'baseline_kwh' => 800]),
    ]));

    expect(BaselineResolver::forFacility($facility))->toBe(800.0);

    $facility->setRelation('energyProfiles', collect());

    expect(BaselineResolver::forFacility($facility))->toBe(700.0);
});
