<?php

namespace App\Services;

use App\Models\EnergyRecord;
use App\Models\MainMeterAlert;
use App\Models\MainMeterBaseline;
use App\Models\MainMeterReading;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MainMeterBaselineAlertService
{
    private const DEMAND_SPIKE_MULTIPLIER = 1.15;

    /**
     * @return array{
     *   baseline_kwh: float|null,
     *   baseline_peak_kw: float|null,
     *   increase_percent: float|null,
     *   alert: MainMeterAlert|null
     * }
     */
    public function processReading(MainMeterReading $reading): array
    {
        $reading->loadMissing([
            'facility.energyProfiles' => fn ($query) => $query->orderByDesc('id'),
            'facility.meters' => fn ($query) => $query
                ->where('meter_type', 'main')
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                ->orderBy('id'),
        ]);

        $historyQuery = MainMeterReading::query()
            ->approved()
            ->where('facility_id', $reading->facility_id)
            ->where('period_type', 'monthly')
            ->whereDate('period_end_date', '<', $reading->period_end_date)
            ->orderByDesc('period_end_date')
            ->orderByDesc('id');

        $lastThree = (clone $historyQuery)->limit(3)->get();
        $lastSix = (clone $historyQuery)->limit(6)->get();

        $periodLabel = $reading->periodLabel();
        $movingAvg3 = $lastThree->count() >= 3 ? $this->averageKwh($lastThree) : null;
        $movingAvg6 = $lastSix->count() >= 6 ? $this->averageKwh($lastSix) : null;

        $movingAvg3PerDay = $this->averageKwhPerDay($lastThree);
        $movingAvg6PerDay = $this->averageKwhPerDay($lastSix);
        $seasonal = $this->computeSeasonalBaseline($reading);
        $seasonalKwh = $seasonal['baseline_kwh'];
        $seasonalPerDay = $seasonal['baseline_kwh_per_day'];
        $normalized = $this->computeNormalizedExpected($lastThree, $reading);
        $baselinePeakKw = $this->averagePeakDemand($lastThree);

        if ($movingAvg3 !== null) {
            $this->upsertBaseline(
                $reading,
                'moving_avg_3',
                $movingAvg3,
                $movingAvg3PerDay ?? 0.0,
                $baselinePeakKw,
                $periodLabel
            );
        }

        if ($movingAvg6 !== null) {
            $this->upsertBaseline(
                $reading,
                'moving_avg_6',
                $movingAvg6,
                $movingAvg6PerDay ?? 0.0,
                $baselinePeakKw,
                $periodLabel
            );
        }

        if ($seasonalKwh !== null) {
            $this->upsertBaseline(
                $reading,
                'seasonal',
                $seasonalKwh,
                $seasonalPerDay ?? 0.0,
                $baselinePeakKw,
                $periodLabel
            );
        }

        $expectedKwh = $normalized['expected_kwh'];
        $baselinePerDay = $normalized['baseline_kwh_per_day'];
        if ($expectedKwh !== null && $baselinePerDay !== null) {
            $this->upsertBaseline(
                $reading,
                'normalized_per_day',
                $expectedKwh,
                $baselinePerDay,
                $baselinePeakKw,
                $periodLabel
            );
        }

        // Default baseline stays moving average 3.
        // If current month has operating_days, use normalized expected kWh for better month-length correction.
        $selectedBaseline = $movingAvg3;
        if ($expectedKwh !== null) {
            $selectedBaseline = $expectedKwh;
        } elseif ($selectedBaseline === null) {
            $selectedBaseline = $seasonalKwh ?? $movingAvg6;
        }

        if ($selectedBaseline === null) {
            $selectedBaseline = $this->resolveFallbackBaselineKwh($reading);
        }

        $alert = $this->detectAndPersistAlert(
            $reading,
            $selectedBaseline,
            $baselinePeakKw,
            $lastThree
        );

        return [
            'baseline_kwh' => $selectedBaseline,
            'baseline_peak_kw' => $baselinePeakKw,
            'increase_percent' => $alert ? (float) $alert->increase_percent : null,
            'alert' => $alert,
        ];
    }

    private function averageKwh(Collection $rows): ?float
    {
        if ($rows->isEmpty()) {
            return null;
        }

        return round((float) $rows->avg('kwh_used'), 2);
    }

    private function averageKwhPerDay(Collection $rows): ?float
    {
        $valid = $rows->filter(function ($row) {
            return is_numeric($row->kwh_used)
                && is_numeric($row->operating_days)
                && (int) $row->operating_days > 0;
        });

        if ($valid->isEmpty()) {
            return null;
        }

        $perDayValues = $valid->map(function ($row) {
            return (float) $row->kwh_used / (int) $row->operating_days;
        });

        return round((float) $perDayValues->avg(), 4);
    }

    /**
     * Seasonal baseline uses same calendar month from previous years.
     * Requires at least 2 historical years to avoid unstable single-year seasonality.
     *
     * @return array{baseline_kwh: float|null, baseline_kwh_per_day: float|null}
     */
    private function computeSeasonalBaseline(MainMeterReading $reading): array
    {
        $month = Carbon::parse($reading->period_end_date)->month;

        $rows = MainMeterReading::query()
            ->approved()
            ->where('facility_id', $reading->facility_id)
            ->where('period_type', 'monthly')
            ->whereMonth('period_end_date', $month)
            ->whereDate('period_end_date', '<', $reading->period_end_date)
            ->get(['kwh_used', 'operating_days', 'period_end_date']);

        if ($rows->isEmpty()) {
            return ['baseline_kwh' => null, 'baseline_kwh_per_day' => null];
        }

        $yearCount = $rows->pluck('period_end_date')
            ->map(fn ($value) => Carbon::parse($value)->year)
            ->unique()
            ->count();

        if ($yearCount < 2) {
            return ['baseline_kwh' => null, 'baseline_kwh_per_day' => null];
        }

        return [
            'baseline_kwh' => round((float) $rows->avg('kwh_used'), 2),
            'baseline_kwh_per_day' => $this->averageKwhPerDay($rows),
        ];
    }

    /**
     * @return array{expected_kwh: float|null, baseline_kwh_per_day: float|null}
     */
    private function computeNormalizedExpected(Collection $lastThree, MainMeterReading $current): array
    {
        if (! is_numeric($current->operating_days) || (int) $current->operating_days <= 0) {
            return ['expected_kwh' => null, 'baseline_kwh_per_day' => null];
        }

        $validPerDayRows = $lastThree->filter(function ($row) {
            return is_numeric($row->kwh_used)
                && is_numeric($row->operating_days)
                && (int) $row->operating_days > 0;
        });

        if ($lastThree->count() < 3 || $validPerDayRows->count() < 3) {
            return ['expected_kwh' => null, 'baseline_kwh_per_day' => null];
        }

        $perDay = $this->averageKwhPerDay($validPerDayRows);
        if ($perDay === null) {
            return ['expected_kwh' => null, 'baseline_kwh_per_day' => null];
        }

        $expected = round($perDay * (int) $current->operating_days, 2);

        return ['expected_kwh' => $expected, 'baseline_kwh_per_day' => $perDay];
    }

    private function averagePeakDemand(Collection $rows): ?float
    {
        $peakRows = $rows->filter(function ($row) {
            return is_numeric($row->peak_demand_kw) && (float) $row->peak_demand_kw > 0;
        });

        if ($peakRows->count() < 3) {
            return null;
        }

        return round((float) $peakRows->avg('peak_demand_kw'), 2);
    }

    private function upsertBaseline(
        MainMeterReading $reading,
        string $baselineType,
        float $baselineKwh,
        float $baselinePerDay,
        ?float $baselinePeakKw,
        string $periodLabel
    ): MainMeterBaseline {
        return MainMeterBaseline::updateOrCreate(
            [
                'facility_id' => $reading->facility_id,
                'baseline_type' => $baselineType,
                'computed_for_period' => $periodLabel,
            ],
            [
                'baseline_kwh' => round($baselineKwh, 2),
                'baseline_kwh_per_day' => round($baselinePerDay, 4),
                'baseline_peak_kw' => $baselinePeakKw !== null ? round($baselinePeakKw, 2) : null,
                'computed_at' => now(),
            ]
        );
    }

    private function detectAndPersistAlert(
        MainMeterReading $reading,
        ?float $baselineKwh,
        ?float $baselinePeakKw,
        Collection $lastThreeApproved
    ): ?MainMeterAlert {
        $currentKwh = is_numeric($reading->kwh_used) ? (float) $reading->kwh_used : 0.0;
        $energyLevel = 'none';
        $increasePercent = null;

        if (is_numeric($baselineKwh) && (float) $baselineKwh > 0) {
            $baselineKwh = (float) $baselineKwh;
            $increasePercent = round((($currentKwh - $baselineKwh) / $baselineKwh) * 100, 2);

            [$warningPercent, $criticalPercent] = $this->resolveEnergyAlertThresholdsByBaseline($baselineKwh);

            if ($increasePercent > $criticalPercent) {
                $energyLevel = 'critical';
            } elseif ($increasePercent > $warningPercent) {
                $energyLevel = 'warning';
            }
        }

        $demandSpike = false;
        $peakIncreasePercent = null;
        if (is_numeric($reading->peak_demand_kw)
            && is_numeric($baselinePeakKw)
            && (float) $reading->peak_demand_kw > 0
            && (float) $baselinePeakKw > 0
        ) {
            $peak = (float) $reading->peak_demand_kw;
            $baselinePeak = (float) $baselinePeakKw;
            $peakIncreasePercent = round((($peak - $baselinePeak) / $baselinePeak) * 100, 2);
            $demandSpike = $peak > ($baselinePeak * self::DEMAND_SPIKE_MULTIPLIER);
        }

        $finalLevel = $energyLevel;
        if ($demandSpike && $finalLevel === 'none') {
            $finalLevel = 'warning';
        }

        if ($finalLevel === 'none') {
            MainMeterAlert::where('main_meter_reading_id', $reading->id)->delete();
            return null;
        }

        $reason = $this->buildReason(
            $reading,
            $baselineKwh,
            $increasePercent,
            $baselinePeakKw,
            $peakIncreasePercent,
            $demandSpike,
            $lastThreeApproved
        );

        return MainMeterAlert::updateOrCreate(
            ['main_meter_reading_id' => $reading->id],
            [
                'facility_id' => $reading->facility_id,
                'baseline_kwh' => $baselineKwh && $baselineKwh > 0 ? round($baselineKwh, 2) : round($currentKwh, 2),
                'current_kwh' => round($currentKwh, 2),
                'increase_percent' => $increasePercent ?? 0.0,
                'alert_level' => $finalLevel,
                'reason' => $reason,
            ]
        );
    }

    private function buildReason(
        MainMeterReading $reading,
        ?float $baselineKwh,
        ?float $increasePercent,
        ?float $baselinePeakKw,
        ?float $peakIncreasePercent,
        bool $demandSpike,
        Collection $lastThreeApproved
    ): string {
        $currentKwh = is_numeric($reading->kwh_used) ? (float) $reading->kwh_used : 0.0;
        $parts = [];

        if ($baselineKwh && $baselineKwh > 0 && $increasePercent !== null) {
            $parts[] = sprintf(
                'Current %.2f kWh is %.2f%% above baseline %.2f kWh.',
                $currentKwh,
                $increasePercent,
                $baselineKwh
            );
        }

        $previous = $lastThreeApproved->first();
        if ($previous && is_numeric($previous->kwh_used) && (float) $previous->kwh_used > 0) {
            $previousKwh = (float) $previous->kwh_used;
            $increasePrev = round((($currentKwh - $previousKwh) / $previousKwh) * 100, 2);
            $parts[] = sprintf('Month-to-month: %.2f%% vs previous %.2f kWh.', $increasePrev, $previousKwh);
        }

        if (is_numeric($reading->operating_days) && (int) $reading->operating_days > 0) {
            $parts[] = sprintf(
                'Daily average: %.2f kWh/day for %d operating days.',
                round($currentKwh / (int) $reading->operating_days, 2),
                (int) $reading->operating_days
            );
        }

        if ($demandSpike && is_numeric($reading->peak_demand_kw) && is_numeric($baselinePeakKw)) {
            $parts[] = sprintf(
                'Demand spike detected: peak %.2f kW vs baseline %.2f kW (%.2f%%).',
                (float) $reading->peak_demand_kw,
                (float) $baselinePeakKw,
                $peakIncreasePercent ?? 0.0
            );
        }

        if (is_numeric($reading->power_factor) && (float) $reading->power_factor > 0) {
            $pf = (float) $reading->power_factor;
            if ($pf < 0.90) {
                $parts[] = sprintf('Power factor is low at %.3f.', $pf);
            } else {
                $parts[] = sprintf('Power factor: %.3f.', $pf);
            }
        }

        if (empty($parts)) {
            $parts[] = 'Alert generated due to abnormal increase versus baseline.';
        }

        return implode(' ', $parts);
    }

    /**
     * Resolve warning/critical percent thresholds from baseline-derived size class.
     * Uses app settings (same source as EnergyRecord) for consistency.
     *
     * @return array{0: float, 1: float}
     */
    private function resolveEnergyAlertThresholdsByBaseline(float $baselineKwh): array
    {
        $thresholds = EnergyRecord::alertThresholdsBySize();
        $sizeKey = EnergyRecord::resolveSizeKeyFromBaseline($baselineKwh);
        $sizeThresholds = $thresholds[$sizeKey] ?? $thresholds['small'] ?? [];

        $warningPercent = is_numeric($sizeThresholds['level2'] ?? null) ? (float) $sizeThresholds['level2'] : 10.0;
        $criticalPercent = is_numeric($sizeThresholds['level5'] ?? null) ? (float) $sizeThresholds['level5'] : 30.0;

        if ($criticalPercent < $warningPercent) {
            $criticalPercent = $warningPercent;
        }

        return [$warningPercent, $criticalPercent];
    }

    private function resolveFallbackBaselineKwh(MainMeterReading $reading): ?float
    {
        $facility = $reading->facility;
        if (! $facility) {
            return null;
        }

        $latestProfile = $facility->energyProfiles->first();
        $baselineKwh = null;

        if ($latestProfile && ! empty($latestProfile->primary_meter_id)) {
            $primaryMainMeter = $facility->meters->firstWhere('id', (int) $latestProfile->primary_meter_id);
            if ($primaryMainMeter && is_numeric($primaryMainMeter->baseline_kwh) && (float) $primaryMainMeter->baseline_kwh > 0) {
                $baselineKwh = (float) $primaryMainMeter->baseline_kwh;
            }
        }

        if ($baselineKwh === null) {
            $fallbackMainMeter = $facility->meters->first(function ($meter) {
                return is_numeric($meter->baseline_kwh) && (float) $meter->baseline_kwh > 0;
            });
            if ($fallbackMainMeter) {
                $baselineKwh = (float) $fallbackMainMeter->baseline_kwh;
            }
        }

        if ($baselineKwh === null && $latestProfile && is_numeric($latestProfile->baseline_kwh) && (float) $latestProfile->baseline_kwh > 0) {
            $baselineKwh = (float) $latestProfile->baseline_kwh;
        } elseif ($baselineKwh === null && is_numeric($facility->baseline_kwh) && (float) $facility->baseline_kwh > 0) {
            $baselineKwh = (float) $facility->baseline_kwh;
        }

        return $baselineKwh !== null ? round($baselineKwh, 2) : null;
    }
}
