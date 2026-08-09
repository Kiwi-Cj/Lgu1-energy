<?php

use App\Support\EnergyAlertRouting;

test('critical and very high usage are owned by the incident workflow', function (string $level) {
    expect(EnergyAlertRouting::owner($level))->toBe(EnergyAlertRouting::INCIDENT);
})->with(['Critical', 'Very High']);

test('only critical usage is automatically routed to maintenance', function () {
    expect(EnergyAlertRouting::requiresMaintenance('Critical'))->toBeTrue()
        ->and(EnergyAlertRouting::requiresMaintenance('Very High'))->toBeFalse()
        ->and(EnergyAlertRouting::requiresMaintenance('High'))->toBeFalse();
});

test('high usage and cost-only risks are owned by conservation', function () {
    expect(EnergyAlertRouting::owner('High'))->toBe(EnergyAlertRouting::CONSERVATION)
        ->and(EnergyAlertRouting::owner('Normal', true))->toBe(EnergyAlertRouting::CONSERVATION);
});

test('normal usage without cost risk remains monitoring only', function () {
    expect(EnergyAlertRouting::owner('Normal'))->toBe(EnergyAlertRouting::MONITOR);
});
