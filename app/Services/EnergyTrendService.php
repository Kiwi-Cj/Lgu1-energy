<?php

namespace App\Services;

use App\Models\EnergyRecord;
use App\Models\Facility;
use Illuminate\Support\Collection;

class EnergyTrendService
{
    public function labelsFor(Collection $selectedRecords): array
    {
        if ($selectedRecords->isEmpty()) {
            return [];
        }

        $facilityIds = $selectedRecords->pluck('facility_id')->filter()->unique()->values();
        $latestPeriod = $selectedRecords->max(fn ($row) => ((int) $row->year * 100) + (int) $row->month);

        $historyRecords = EnergyRecord::with('facility')
            ->whereIn('facility_id', $facilityIds)
            ->where(function ($mainScope) {
                $mainScope->whereNull('meter_id')
                    ->orWhereHas('meter', fn ($meter) => $meter->where('meter_type', 'main'));
            })
            ->whereRaw('(year * 100 + month) <= ?', [(int) $latestPeriod])
            ->orderBy('year')
            ->orderBy('month')
            ->orderBy('id')
            ->get();

        $selectedIds = $selectedRecords->pluck('id')->map(fn ($id) => (int) $id)->flip();
        $labels = [];

        $historyRecords
            ->groupBy(fn ($row) => (int) $row->facility_id . ':' . (int) ($row->meter_id ?? 0))
            ->each(function ($meterRecords) use (&$labels, $selectedIds) {
                $history = [];

                foreach ($meterRecords as $record) {
                    $actual = is_numeric($record->actual_kwh ?? null) ? (float) $record->actual_kwh : 0.0;
                    $reference = count($history) >= 3
                        ? array_sum(array_slice($history, -3)) / 3
                        : (count($history) >= 1 ? end($history) : null);

                    $label = 'insufficient';
                    if ($reference !== null && $reference > 0) {
                        $baseline = is_numeric($record->baseline_kwh ?? null) ? (float) $record->baseline_kwh : null;
                        $facilityBaseline = is_numeric($record->facility?->baseline_kwh ?? null) ? (float) $record->facility->baseline_kwh : null;
                        $size = Facility::resolveSizeLabelFromBaseline($baseline ?? $facilityBaseline) ?? 'Small';
                        $threshold = $this->thresholdForSize($size);
                        $change = (($actual - $reference) / $reference) * 100;
                        $label = $change > $threshold ? 'up' : ($change < -$threshold ? 'down' : 'stable');
                    }

                    if ($selectedIds->has((int) $record->id)) {
                        $labels[$record->id] = $label;
                    }

                    if ($actual > 0) {
                        $history[] = $actual;
                    }
                }
            });

        return $labels;
    }

    public function displayLabel(string $key): string
    {
        return match ($key) {
            'up' => 'Increasing',
            'down' => 'Decreasing',
            'stable' => 'Stable',
            default => 'Insufficient Historical Data',
        };
    }

    private function thresholdForSize(string $size): float
    {
        return match (strtolower(str_replace(['_', ' '], '-', trim($size)))) {
            'xlarge', 'extra-large' => 2.0,
            'large' => 4.0,
            'medium' => 7.0,
            default => 10.0,
        };
    }
}
