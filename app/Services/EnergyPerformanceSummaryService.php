<?php

namespace App\Services;

use App\Models\EnergyRecord;
use App\Support\BaselineResolver;
use Illuminate\Support\Collection;

class EnergyPerformanceSummaryService
{
    public function build(Collection $facilities, ?string $statusFilter = null): array
    {
        $recordsByFacility = EnergyRecord::query()
            ->with(['meter', 'facility.energyProfiles'])
            ->whereIn('facility_id', $facilities->pluck('id'))
            ->where('review_status', 'approved')
            ->where(function ($query) {
                $query->whereNull('meter_id')
                    ->orWhereHas('meter', fn ($meter) => $meter->where('meter_type', 'main'));
            })
            ->whereNotNull('actual_kwh')
            ->get(['id', 'facility_id', 'meter_id', 'year', 'month', 'actual_kwh', 'baseline_kwh', 'input_source', 'external_source'])
            ->groupBy('facility_id');

        $rows = [];
        foreach ($facilities as $facility) {
            $records = $recordsByFacility->get($facility->id, collect());
            $periods = $records
                ->groupBy(fn ($record) => sprintf('%04d-%02d', (int) $record->year, (int) $record->month))
                ->map(function ($periodRows, string $period) {
                    // Prefer meter-linked totals over a duplicate legacy
                    // facility-level total for the same reporting period.
                    $linkedRows = $periodRows->whereNotNull('meter_id');
                    $selectedRows = $linkedRows->isNotEmpty() ? $linkedRows : $periodRows;
                    $resolvedBaselines = $selectedRows->map(
                        fn ($row) => BaselineResolver::forRecord($row, $row->facility)
                    );
                    $hasCompleteBaseline = $selectedRows->isNotEmpty()
                        && $resolvedBaselines->every(fn ($baseline) => is_numeric($baseline) && (float) $baseline > 0);
                    $isUmanReading = $selectedRows->contains(
                        fn ($row) => str_starts_with(strtolower((string) $row->external_source), 'uman')
                    );
                    $isCprfReading = $selectedRows->contains(
                        fn ($row) => strtolower((string) $row->input_source) === 'cprf'
                    );

                    return [
                        'period' => $period,
                        'actual_kwh' => round((float) $selectedRows->sum('actual_kwh'), 2),
                        'baseline_kwh' => $hasCompleteBaseline
                            ? round((float) $resolvedBaselines->sum(), 2)
                            : null,
                        'reading_source' => $isUmanReading ? 'UMAN' : ($isCprfReading ? 'CPRF' : 'Manual'),
                    ];
                })
                ->sortKeysDesc();

            $latest = $periods->first();
            $actualKwh = $latest['actual_kwh'] ?? null;
            $baselineKwh = $latest['baseline_kwh'] ?? null;
            $deviation = EnergyRecord::calculateDeviation($actualKwh, $baselineKwh);
            $alert = $baselineKwh !== null
                ? EnergyRecord::resolveAlertLevel($deviation, $baselineKwh)
                : null;
            $status = $this->status($latest, $baselineKwh, $alert);

            if ($statusFilter && $statusFilter !== 'all' && $statusFilter !== $status) {
                continue;
            }

            $rows[] = [
                'facility_id' => $facility->id,
                'facility' => $facility->name,
                'source' => $facility->isCprfManaged() ? 'CPRF' : 'Manual',
                'reading_source' => $latest['reading_source'] ?? null,
                'latest_period' => $latest
                    ? date('M Y', strtotime($latest['period'].'-01'))
                    : 'No approved readings',
                'actual_kwh' => $actualKwh,
                'baseline_kwh' => $baselineKwh,
                'deviation' => $deviation,
                'alert' => $alert ?: 'No Data',
                'months_count' => $periods->count(),
                'status' => $status,
                'status_label' => match ($status) {
                    'requires_action' => 'Requires Action',
                    'monitoring' => 'Monitoring',
                    default => 'Data Required',
                },
                'assessment' => $this->assessment($latest, $baselineKwh, $deviation, $alert),
                'recommendation' => $this->recommendation($latest, $baselineKwh, $alert),
            ];
        }

        $rowCollection = collect($rows)
            ->sortBy(fn (array $row) => sprintf(
                '%d-%s',
                match ($row['status']) {
                    'requires_action' => 1,
                    'data_required' => 2,
                    default => 3,
                },
                strtolower($row['facility'])
            ))
            ->values();

        return [
            'rows' => $rowCollection->all(),
            'facilities_count' => $rowCollection->count(),
            'with_readings_count' => $rowCollection->whereNotNull('actual_kwh')->count(),
            'assessment_ready_count' => $rowCollection
                ->filter(fn (array $row) => $row['actual_kwh'] !== null && $row['baseline_kwh'] !== null)
                ->count(),
            'missing_reading_count' => $rowCollection->whereNull('actual_kwh')->count(),
            'missing_baseline_count' => $rowCollection
                ->filter(fn (array $row) => $row['actual_kwh'] !== null && $row['baseline_kwh'] === null)
                ->count(),
            'requires_action_count' => $rowCollection->where('status', 'requires_action')->count(),
            'monitoring_count' => $rowCollection->where('status', 'monitoring')->count(),
            'data_required_count' => $rowCollection->where('status', 'data_required')->count(),
        ];
    }

    private function status(?array $latest, ?float $baselineKwh, ?string $alert): string
    {
        if ($latest === null || $baselineKwh === null) {
            return 'data_required';
        }

        return in_array($alert, ['Warning', 'High', 'Very High', 'Critical', 'Drop Warning', 'Drop High', 'Drop Critical'], true)
            ? 'requires_action'
            : 'monitoring';
    }

    private function assessment(?array $latest, ?float $baselineKwh, ?float $deviation, ?string $alert): string
    {
        if ($latest === null) {
            return 'No approved monthly energy reading is available.';
        }
        if ($baselineKwh === null) {
            return 'The latest approved consumption is available, but no complete approved baseline exists for comparison.';
        }
        if (str_starts_with((string) $alert, 'Drop')) {
            return 'Consumption is '.number_format(abs((float) $deviation), 2).'% below baseline. Validate the reading and confirm whether savings or an operational change caused the drop.';
        }
        if ($deviation !== null && $deviation > 0) {
            return 'Consumption is '.number_format($deviation, 2).'% above baseline with a '.$alert.' alert level.';
        }

        return 'Consumption is within or below the expected baseline range.';
    }

    private function recommendation(?array $latest, ?float $baselineKwh, ?string $alert): string
    {
        if ($latest === null) {
            return 'Submit and approve the next monthly main-meter reading.';
        }
        if ($baselineKwh === null) {
            return 'Complete and approve the main-meter baseline so deviation and spike assessment can begin.';
        }

        return match ($alert) {
            'Critical', 'Very High' => 'Validate the bill and meter, inspect major loads immediately, and assign a corrective action.',
            'High', 'Warning' => 'Review operating schedules and major equipment, then monitor the meter weekly until usage stabilizes.',
            'Drop Critical', 'Drop High', 'Drop Warning' => 'Validate the low reading and document confirmed conservation savings or operational downtime.',
            default => 'Continue monthly monitoring and retain the current energy controls.',
        };
    }
}
