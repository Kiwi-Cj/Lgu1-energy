<?php

use App\Support\SystemSettings;

test('system settings normalize supported facility image extensions', function () {
    expect(SystemSettings::facilityImageExtensions('JPG, png webp,exe,jpg'))
        ->toBe(['jpg', 'png', 'webp'])
        ->and(SystemSettings::invalidFacilityImageExtensions('jpg,exe,svg'))
        ->toBe(['exe', 'svg']);
});

test('system settings report the actual number of alert threshold inputs', function () {
    expect(SystemSettings::thresholdInputCount())->toBe(36);
});
