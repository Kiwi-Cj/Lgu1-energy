<?php

namespace App\Services;

use App\Models\EnergyRecord;
use App\Models\FacilityMeter;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MainMeterBaselineEstablishmentService
{
    public const MIN_MONTHS = 3;

    public const RECOMMENDED_MONTHS = 6;

    /**
     * @return array{
     *   has_baseline: bool,
     *   current_baseline: float|null,
     *   approved_reading_count: int,
     *   usable_reading_count: int,
     *   suggested_months: int,
     *   candidate_kwh: float|null,
     *   periods: array<int, array{label:string,actual_kwh:float}>,
     *   can_establish: bool,
     *   status: string
     * }
     */
    public function summary(FacilityMeter $meter): array
    {
        $readings = $this->approvedMonthlyReadings($meter);
        $usable = $readings->take(self::RECOMMENDED_MONTHS);
        $suggestedMonths = min(self::RECOMMENDED_MONTHS, $usable->count());
        $hasBaseline = is_numeric($meter->baseline_kwh) && (float) $meter->baseline_kwh > 0;
        $candidate = $suggestedMonths >= self::MIN_MONTHS
            ? round((float) $usable->take($suggestedMonths)->avg('actual_kwh'), 2)
            : null;

        return [
            'has_baseline' => $hasBaseline,
            'current_baseline' => $hasBaseline ? round((float) $meter->baseline_kwh, 2) : null,
            'approved_reading_count' => $readings->count(),
            'usable_reading_count' => $usable->count(),
            'suggested_months' => $suggestedMonths,
            'candidate_kwh' => $candidate,
            'periods' => $usable
                ->sortBy(fn (EnergyRecord $record) => sprintf('%04d-%02d', $record->year, $record->month))
                ->values()
                ->map(fn (EnergyRecord $record) => [
                    'label' => date('M Y', mktime(0, 0, 0, (int) $record->month, 1, (int) $record->year)),
                    'actual_kwh' => round((float) $record->actual_kwh, 2),
                ])
                ->all(),
            'can_establish' => ! $hasBaseline && $suggestedMonths >= self::MIN_MONTHS,
            'status' => $hasBaseline
                ? 'established'
                : ($suggestedMonths >= self::RECOMMENDED_MONTHS
                    ? 'recommended_ready'
                    : ($suggestedMonths >= self::MIN_MONTHS ? 'preliminary_ready' : 'collecting')),
        ];
    }

    /**
     * @return array{baseline_kwh:float, months:int, start_period:string, end_period:string}
     */
    public function establish(FacilityMeter $meter, int $months): array
    {
        if (strtolower((string) $meter->meter_type) !== 'main' || ! $meter->approved_at) {
            throw ValidationException::withMessages([
                'baseline_months' => 'Only an approved main meter can establish a baseline.',
            ]);
        }

        if (is_numeric($meter->baseline_kwh) && (float) $meter->baseline_kwh > 0) {
            throw ValidationException::withMessages([
                'baseline_months' => 'This main meter already has an approved baseline. Edit the meter to change it.',
            ]);
        }

        $readings = $this->approvedMonthlyReadings($meter)->take($months);
        if ($readings->count() < $months) {
            throw ValidationException::withMessages([
                'baseline_months' => "{$months} approved monthly readings are required before this baseline can be approved.",
            ]);
        }

        $baseline = round((float) $readings->avg('actual_kwh'), 2);
        if ($baseline <= 0) {
            throw ValidationException::withMessages([
                'baseline_months' => 'The selected readings do not produce a valid baseline.',
            ]);
        }

        $periodKeys = $readings
            ->map(fn (EnergyRecord $record) => sprintf('%04d-%02d', $record->year, $record->month))
            ->sort()
            ->values();

        return [
            'baseline_kwh' => $baseline,
            'months' => $months,
            'start_period' => (string) $periodKeys->first(),
            'end_period' => (string) $periodKeys->last(),
        ];
    }

    private function approvedMonthlyReadings(FacilityMeter $meter): Collection
    {
        return EnergyRecord::query()
            ->where('facility_id', $meter->facility_id)
            ->where('meter_id', $meter->id)
            ->where('review_status', 'approved')
            ->whereNotNull('actual_kwh')
            ->where('actual_kwh', '>', 0)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('id')
            ->get(['id', 'year', 'month', 'actual_kwh'])
            ->unique(fn (EnergyRecord $record) => sprintf('%04d-%02d', $record->year, $record->month))
            ->values();
    }
}
