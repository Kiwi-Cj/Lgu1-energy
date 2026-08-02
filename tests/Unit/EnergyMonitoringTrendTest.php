<?php

use App\Http\Controllers\Modules\EnergyMonitoringController;
use App\Services\EnergyRecommendationService;
use Illuminate\Support\Collection;

function calculateMonitoringTrend(Collection $records, int $year, int $month): array
{
    $controller = new EnergyMonitoringController(new EnergyRecommendationService());
    $method = new ReflectionMethod($controller, 'calculateTrendPercent');

    return $method->invoke($controller, $records, $year, $month);
}

function detectsMonitoringSpike(Collection $records, float $baseline, float $threshold): bool
{
    $controller = new EnergyMonitoringController(new EnergyRecommendationService());
    $method = new ReflectionMethod($controller, 'hasThreeMonthSpike');

    return $method->invoke($controller, $records, 2026, 8, $baseline, $threshold);
}

test('missing current-period reading is not displayed as negative one hundred percent', function () {
    $records = collect([
        (object) ['year' => 2026, 'month' => 7, 'actual_kwh' => 100],
        (object) ['year' => 2026, 'month' => 6, 'actual_kwh' => 90],
    ]);

    expect(calculateMonitoringTrend($records, 2026, 8))
        ->toBe([null, '-']);
});

test('an actual zero reading remains a valid calculated decrease', function () {
    $records = collect([
        (object) ['year' => 2026, 'month' => 8, 'actual_kwh' => 0],
        (object) ['year' => 2026, 'month' => 7, 'actual_kwh' => 100],
    ]);

    [$percent, $display] = calculateMonitoringTrend($records, 2026, 8);

    expect($percent)->toBe(-100.0)
        ->and($display)->toBe('-100.00%');
});

test('small consecutive fluctuations are not treated as a spike', function () {
    $records = collect([
        (object) ['year' => 2026, 'month' => 6, 'actual_kwh' => 100],
        (object) ['year' => 2026, 'month' => 7, 'actual_kwh' => 101],
        (object) ['year' => 2026, 'month' => 8, 'actual_kwh' => 102],
    ]);

    expect(detectsMonitoringSpike($records, 100, 10))->toBeFalse();
});

test('a spike must exceed both its trend threshold and facility baseline', function () {
    $meaningfulSpike = collect([
        (object) ['year' => 2026, 'month' => 6, 'actual_kwh' => 80],
        (object) ['year' => 2026, 'month' => 7, 'actual_kwh' => 95],
        (object) ['year' => 2026, 'month' => 8, 'actual_kwh' => 115],
    ]);
    $stillBelowBaseline = collect([
        (object) ['year' => 2026, 'month' => 6, 'actual_kwh' => 60],
        (object) ['year' => 2026, 'month' => 7, 'actual_kwh' => 70],
        (object) ['year' => 2026, 'month' => 8, 'actual_kwh' => 80],
    ]);

    expect(detectsMonitoringSpike($meaningfulSpike, 100, 10))->toBeTrue()
        ->and(detectsMonitoringSpike($stillBelowBaseline, 100, 10))->toBeFalse();
});

test('drop alerts retain actionable severity ranks', function () {
    $controller = new EnergyMonitoringController(new EnergyRecommendationService());
    $method = new ReflectionMethod($controller, 'resolveAlertLevelRank');

    expect($method->invoke($controller, 'Drop Warning'))->toBe(2)
        ->and($method->invoke($controller, 'Drop High'))->toBe(3)
        ->and($method->invoke($controller, 'Drop Critical'))->toBe(5);
});
