<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\Setting;

test('saved drop thresholds are loaded by the alert engine', function () {
    Setting::setValue('alert_drop_level1_small', '6');
    Setting::setValue('alert_drop_level2_small', '12');
    Setting::setValue('alert_drop_level3_small', '18');

    $cache = new ReflectionProperty(EnergyRecord::class, 'thresholdCache');
    $cache->setValue(null, null);

    expect(EnergyRecord::alertThresholdsBySize()['small']['drop'])
        ->toBe(['level1' => 6.0, 'level2' => 12.0, 'level3' => 18.0]);
});

test('saved baseline size bands control threshold classification', function () {
    Setting::setValue('baseline_small_max_kwh', '500');
    Setting::setValue('baseline_medium_max_kwh', '1500');
    Setting::setValue('baseline_large_max_kwh', '5000');

    expect(EnergyRecord::resolveSizeKeyFromBaseline(500))->toBe('small')
        ->and(EnergyRecord::resolveSizeKeyFromBaseline(501))->toBe('medium')
        ->and(EnergyRecord::resolveSizeKeyFromBaseline(1501))->toBe('large')
        ->and(EnergyRecord::resolveSizeKeyFromBaseline(5001))->toBe('xlarge')
        ->and(Facility::resolveSizeLabelFromBaseline(500))->toBe('Small')
        ->and(Facility::resolveSizeLabelFromBaseline(501))->toBe('Medium')
        ->and(Facility::resolveSizeLabelFromBaseline(1501))->toBe('Large')
        ->and(Facility::resolveSizeLabelFromBaseline(5001))->toBe('Extra Large');
});

test('legacy settings api is not publicly exposed', function () {
    $this->getJson('/api/settings')->assertNotFound();
    $this->postJson('/api/settings', ['system_name' => 'Changed'])->assertNotFound();
});
