<?php

namespace App\Services;

use App\Models\EnergyRecord;
use App\Models\SubmeterAlert;
use App\Models\SubmeterBaseline;
use App\Models\SubmeterEquipment;
use App\Models\SubmeterReading;
use App\Models\User;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class SubmeterBaselineAlertService
{
    /**
     * Compute baselines for the reading period and create/update alert when thresholds are exceeded.
     *
     * @return array{baseline_kwh: float|null, increase_percent: float|null, alert: SubmeterAlert|null}
     */
    public function processReading(SubmeterReading $reading): array
    {
        $reading->loadMissing('submeter.facility');

        // Baseline and alert generation must run only for approved readings.
        if (! $reading->approved_at) {
            SubmeterAlert::where('submeter_reading_id', $reading->id)->delete();
            return [
                'baseline_kwh' => null,
                'increase_percent' => null,
                'alert' => null,
            ];
        }

        $historyQuery = SubmeterReading::query()
            ->approved()
            ->where('submeter_id', $reading->submeter_id)
            ->where('period_type', $reading->period_type)
            ->whereDate('period_end_date', '<', $reading->period_end_date)
            ->orderByDesc('period_end_date')
            ->orderByDesc('id');

        $lastThree = (clone $historyQuery)->limit(3)->get();
        $lastSix = (clone $historyQuery)->limit(6)->get();

        $periodLabel = $reading->periodLabel();
        $movingAvg3 = $this->averageKwh($lastThree);
        $movingAvg3PerDay = $this->averageKwhPerDay($lastThree);
        $movingAvg6 = $this->averageKwh($lastSix);
        $movingAvg6PerDay = $this->averageKwhPerDay($lastSix);
        $seasonal = $this->computeSeasonalBaseline($reading);
        $normalized = $this->computeNormalizedExpected($lastThree, $reading);
        $equipmentEstimate = $this->resolveEquipmentEstimateBaselineKwh($reading);

        if ($movingAvg3 !== null) {
            $this->upsertBaseline(
                $reading,
                'moving_avg_3',
                3,
                $movingAvg3,
                $movingAvg3PerDay,
                $periodLabel
            );
        }

        if ($movingAvg6 !== null && $lastSix->count() >= 3) {
            $this->upsertBaseline(
                $reading,
                'moving_avg_6',
                6,
                $movingAvg6,
                $movingAvg6PerDay,
                $periodLabel
            );
        }

        if ($seasonal !== null) {
            $this->upsertBaseline(
                $reading,
                'seasonal_month',
                12,
                $seasonal,
                null,
                $periodLabel
            );
        }

        $normalizedExpectedKwh = $normalized['expected_kwh'];
        $normalizedPerDay = $normalized['baseline_kwh_per_day'];

        if ($normalizedExpectedKwh !== null) {
            $this->upsertBaseline(
                $reading,
                'normalized_per_day',
                3,
                $normalizedExpectedKwh,
                $normalizedPerDay,
                $periodLabel
            );
        }

        $selectedBaseline = null;
        $selectedBaselineType = null;
        if ($normalizedExpectedKwh !== null) {
            $selectedBaseline = $normalizedExpectedKwh;
            $selectedBaselineType = 'normalized_per_day';
        } elseif ($movingAvg3 !== null) {
            $selectedBaseline = $movingAvg3;
            $selectedBaselineType = 'moving_avg_3';
        } elseif ($seasonal !== null) {
            $selectedBaseline = $seasonal;
            $selectedBaselineType = 'seasonal_month';
        } elseif ($movingAvg6 !== null) {
            $selectedBaseline = $movingAvg6;
            $selectedBaselineType = 'moving_avg_6';
        } elseif ($equipmentEstimate !== null) {
            $selectedBaseline = $equipmentEstimate;
            $selectedBaselineType = 'equipment_estimate';
        }

        if ($selectedBaseline !== null && $selectedBaselineType === 'equipment_estimate') {
            $this->upsertBaseline(
                $reading,
                'equipment_estimate',
                null,
                $equipmentEstimate,
                null,
                $periodLabel
            );
        }

        if ($selectedBaseline !== null) {
            $normalizedPerSqm = $this->computePerSqm($reading, $selectedBaseline);
            if ($normalizedPerSqm !== null) {
                $this->upsertBaseline(
                    $reading,
                    'normalized_per_sqm',
                    3,
                    $selectedBaseline,
                    $normalizedPerSqm,
                    $periodLabel
                );
            }
        }

        $alert = $this->detectAndPersistAlert($reading, $selectedBaseline, $lastThree);
        $increasePercent = $alert ? (float) $alert->increase_percent : null;

        return [
            'baseline_kwh' => $selectedBaseline,
            'increase_percent' => $increasePercent,
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
     * @return array{expected_kwh: float|null, baseline_kwh_per_day: float|null}
     */
    private function computeNormalizedExpected(Collection $lastThree, SubmeterReading $current): array
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

    private function computeSeasonalBaseline(SubmeterReading $reading): ?float
    {
        if ($reading->period_type !== 'monthly') {
            return null;
        }

        $month = Carbon::parse($reading->period_end_date)->month;

        $rows = SubmeterReading::query()
            ->approved()
            ->where('submeter_id', $reading->submeter_id)
            ->where('period_type', 'monthly')
            ->whereMonth('period_end_date', $month)
            ->whereDate('period_end_date', '<', $reading->period_end_date)
            ->get(['kwh_used']);

        if ($rows->isEmpty()) {
            return null;
        }

        return round((float) $rows->avg('kwh_used'), 2);
    }

    private function resolveEquipmentEstimateBaselineKwh(SubmeterReading $reading): ?float
    {
        $estimated = SubmeterEquipment::query()
            ->where('meter_scope', 'sub')
            ->where('submeter_id', (int) $reading->submeter_id)
            ->sum('estimated_kwh');

        if (! is_numeric($estimated) || (float) $estimated <= 0) {
            return null;
        }

        return round((float) $estimated, 2);
    }

    private function computePerSqm(SubmeterReading $reading, float $baselineKwh): ?float
    {
        $facility = $reading->submeter?->facility;
        if (! $facility) {
            return null;
        }

        $area = is_numeric($facility->floor_area_sqm ?? null)
            ? (float) $facility->floor_area_sqm
            : (is_numeric($facility->floor_area ?? null) ? (float) $facility->floor_area : null);

        if (! $area || $area <= 0) {
            return null;
        }

        return round($baselineKwh / $area, 4);
    }

    private function upsertBaseline(
        SubmeterReading $reading,
        string $baselineType,
        ?int $monthsWindow,
        float $baselineKwh,
        ?float $normalizedValue,
        string $periodLabel
    ): SubmeterBaseline {
        return SubmeterBaseline::updateOrCreate(
            [
                'submeter_id' => $reading->submeter_id,
                'baseline_type' => $baselineType,
                'computed_for_period' => $periodLabel,
            ],
            [
                'months_window' => $monthsWindow,
                'baseline_value_kwh' => round($baselineKwh, 2),
                'baseline_value_normalized' => $normalizedValue !== null ? round($normalizedValue, 4) : null,
                'computed_at' => now(),
            ]
        );
    }

    private function detectAndPersistAlert(
        SubmeterReading $reading,
        ?float $baselineKwh,
        Collection $lastThreeApproved
    ): ?SubmeterAlert {
        if (! is_numeric($baselineKwh) || (float) $baselineKwh <= 0) {
            SubmeterAlert::where('submeter_reading_id', $reading->id)->delete();
            return null;
        }

        $baselineKwh = (float) $baselineKwh;
        $currentKwh = is_numeric($reading->kwh_used) ? (float) $reading->kwh_used : 0.0;
        $increasePercent = round((($currentKwh - $baselineKwh) / $baselineKwh) * 100, 2);
        $threshold = $this->resolveThresholdByBaseline($baselineKwh);
        $warningPercent = (float) $threshold['warning_percent'];
        $criticalPercent = (float) $threshold['critical_percent'];
        $sizeLabel = (string) $threshold['label'];

        $alertLevel = 'none';
        if ($increasePercent > $criticalPercent) {
            $alertLevel = 'critical';
        } elseif ($increasePercent > $warningPercent) {
            $alertLevel = 'warning';
        }

        if ($alertLevel === 'none') {
            SubmeterAlert::where('submeter_reading_id', $reading->id)->delete();
            return null;
        }

        $reason = $this->buildReason(
            $reading,
            $baselineKwh,
            $increasePercent,
            $lastThreeApproved,
            $sizeLabel,
            $warningPercent,
            $criticalPercent
        );

        $alert = SubmeterAlert::updateOrCreate(
            ['submeter_reading_id' => $reading->id],
            [
                'submeter_id' => $reading->submeter_id,
                'baseline_value_kwh' => $baselineKwh,
                'current_value_kwh' => round($currentKwh, 2),
                'increase_percent' => $increasePercent,
                'alert_level' => $alertLevel,
                'reason' => $reason,
            ]
        );

        $this->notifyRecipientsOfAlert($reading, $alert);

        return $alert;
    }

    private function buildReason(
        SubmeterReading $reading,
        float $baselineKwh,
        float $increasePercent,
        Collection $lastThreeApproved,
        string $sizeLabel,
        float $warningPercent,
        float $criticalPercent
    ): string {
        $currentKwh = is_numeric($reading->kwh_used) ? (float) $reading->kwh_used : 0.0;
        $parts = [];
        $parts[] = sprintf(
            'Current %.2f kWh is %.2f%% above baseline %.2f kWh.',
            $currentKwh,
            $increasePercent,
            $baselineKwh
        );
        $parts[] = sprintf(
            'Baseline size: %s (warning > %.2f%%, critical > %.2f%%).',
            $sizeLabel,
            $warningPercent,
            $criticalPercent
        );

        $previous = $lastThreeApproved->first();
        if ($previous && is_numeric($previous->kwh_used) && (float) $previous->kwh_used > 0) {
            $previousKwh = (float) $previous->kwh_used;
            $increasePrev = round((($currentKwh - $previousKwh) / $previousKwh) * 100, 2);
            $parts[] = sprintf('Versus previous period: %.2f%% (prev %.2f kWh).', $increasePrev, $previousKwh);
        }

        $facility = $reading->submeter?->facility;
        $area = $facility && is_numeric($facility->floor_area_sqm ?? null)
            ? (float) $facility->floor_area_sqm
            : ($facility && is_numeric($facility->floor_area ?? null) ? (float) $facility->floor_area : null);

        if ($area && $area > 0) {
            $parts[] = sprintf('Current intensity: %.4f kWh/sqm.', round($currentKwh / $area, 4));
        }

        if (is_numeric($reading->operating_days) && (int) $reading->operating_days > 0) {
            $parts[] = sprintf(
                'Current daily average: %.2f kWh/day (%d operating days).',
                round($currentKwh / (int) $reading->operating_days, 2),
                (int) $reading->operating_days
            );
        }

        return implode(' ', $parts);
    }

    /**
     * @return array{label: string, warning_percent: float, critical_percent: float}
     */
    private function resolveThresholdByBaseline(float $baselineKwh): array
    {
        $thresholds = EnergyRecord::alertThresholdsBySize();
        $sizeKey = EnergyRecord::resolveSizeKeyFromBaseline($baselineKwh);
        $sizeThresholds = $thresholds[$sizeKey] ?? $thresholds['small'] ?? [];

        $warningPercent = is_numeric($sizeThresholds['level2'] ?? null) ? (float) $sizeThresholds['level2'] : 10.0;
        $criticalPercent = is_numeric($sizeThresholds['level5'] ?? null) ? (float) $sizeThresholds['level5'] : 30.0;
        if ($criticalPercent < $warningPercent) {
            $criticalPercent = $warningPercent;
        }

        $label = match ($sizeKey) {
            'small' => 'Small',
            'medium' => 'Medium',
            'large' => 'Large',
            'xlarge' => 'Extra Large',
            default => 'Small',
        };

        return [
            'label' => $label,
            'warning_percent' => $warningPercent,
            'critical_percent' => $criticalPercent,
        ];
    }

    private function notifyRecipientsOfAlert(SubmeterReading $reading, SubmeterAlert $alert): void
    {
        try {
            if (! Schema::hasTable('users') || ! Schema::hasTable('notifications')) {
                return;
            }

            $reading->loadMissing('submeter.facility');

            $facilityName = trim((string) ($reading->submeter?->facility?->name ?? 'Unknown Facility'));
            $submeterName = trim((string) ($reading->submeter?->submeter_name ?? 'Submeter'));
            $periodLabel = $reading->periodLabel();
            $level = strtoupper((string) ($alert->alert_level ?? 'warning'));
            $increasePercent = is_numeric($alert->increase_percent) ? round((float) $alert->increase_percent, 2) : null;

            $title = 'Submeter Alert';
            $message = $increasePercent !== null
                ? "Submeter {$submeterName} kWh increased at {$facilityName} ({$periodLabel}) by {$increasePercent}% [{$level}]"
                : "Submeter {$submeterName} kWh increased at {$facilityName} ({$periodLabel}) [{$level}]";

            $recipients = User::query()
                ->with('facilities:id')
                ->get()
                ->filter(function (User $user) use ($reading) {
                    $role = RoleAccess::normalize($user);

                    if (in_array($role, ['super_admin', 'admin', 'energy_officer', 'engineer'], true)) {
                        return true;
                    }

                    if ($role === 'staff' && $reading->submeter?->facility_id) {
                        return $user->facilities->contains('id', (int) $reading->submeter->facility_id);
                    }

                    return false;
                });

            foreach ($recipients as $recipient) {
                $exists = $recipient->notifications()
                    ->where('type', 'submeter_alert')
                    ->where('message', $message)
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();

                if ($exists) {
                    continue;
                }

                $recipient->notifications()->create([
                    'title' => $title,
                    'message' => $message,
                    'type' => 'submeter_alert',
                ]);
            }
        } catch (\Throwable $e) {
            // Notification failure must not block alert persistence.
        }
    }
}
