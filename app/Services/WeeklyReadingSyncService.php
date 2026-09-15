<?php

namespace App\Services;

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\FacilityMeterWeeklyReading;

class WeeklyReadingSyncService
{
    /**
     * Synchronize weekly readings into a monthly EnergyRecord when 4/4 weeks are present.
     * If fewer than 4 weeks exist, ensures partial data does not finalize into a monthly total.
     */
    public function syncMonthlyAggregate(int $facilityId, int $meterId, int $year, int $month): array
    {
        $weeklyReadings = FacilityMeterWeeklyReading::where('facility_id', $facilityId)
            ->where('meter_id', $meterId)
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('week_number')
            ->get();

        $count = $weeklyReadings->count();
        $enteredKwh = (float) $weeklyReadings->sum('actual_kwh');
        $enteredCost = (float) $weeklyReadings->sum('cost');

        // Check for existing monthly record with input_source = 'weekly_aggregate'
        $existingAggregateRecord = EnergyRecord::where('facility_id', $facilityId)
            ->where('meter_id', $meterId)
            ->where('year', $year)
            ->where('month', $month)
            ->where('input_source', 'weekly_aggregate')
            ->first();

        if ($count === 4) {
            $facility = Facility::find($facilityId);
            $meter = FacilityMeter::find($meterId);

            $baselineKwh = null;
            if ($meter && is_numeric($meter->baseline_kwh)) {
                $baselineKwh = (float) $meter->baseline_kwh;
            } elseif ($facility && is_numeric($facility->baseline_kwh)) {
                $baselineKwh = (float) $facility->baseline_kwh;
            }

            $rate = $enteredKwh > 0 ? round($enteredCost / $enteredKwh, 2) : 12.00;
            $firstWeek = $weeklyReadings->first();
            $lastWeek = $weeklyReadings->last();
            $prevDial = $firstWeek && $firstWeek->previous_reading_kwh !== null ? (float) $firstWeek->previous_reading_kwh : null;
            $currDial = $lastWeek && $lastWeek->current_reading_kwh !== null ? (float) $lastWeek->current_reading_kwh : null;

            $record = EnergyRecord::withoutEvents(function () use (
                $existingAggregateRecord,
                $facilityId,
                $meterId,
                $year,
                $month,
                $enteredKwh,
                $enteredCost,
                $rate,
                $baselineKwh,
                $prevDial,
                $currDial
            ) {
                return EnergyRecord::updateOrCreate(
                    [
                        'facility_id' => $facilityId,
                        'meter_id' => $meterId,
                        'year' => $year,
                        'month' => $month,
                        'input_source' => 'weekly_aggregate',
                    ],
                    [
                        'actual_kwh' => $enteredKwh,
                        'previous_reading_kwh' => $prevDial,
                        'current_reading_kwh' => $currDial,
                        'energy_cost' => $enteredCost,
                        'rate_per_kwh' => $rate,
                        'baseline_kwh' => $baselineKwh,
                        'review_status' => 'approved',
                        'recorded_by' => auth()->id(),
                    ]
                );
            });

            return [
                'status' => 'complete',
                'is_complete' => true,
                'weeks_count' => 4,
                'total_kwh' => $enteredKwh,
                'total_cost' => $enteredCost,
                'synced_record_id' => $record->id,
            ];
        }

        // If fewer than 4 weeks, remove any previously generated weekly aggregate record
        if ($existingAggregateRecord) {
            EnergyRecord::withoutEvents(function () use ($existingAggregateRecord) {
                $existingAggregateRecord->forceDelete();
            });
        }

        return [
            'status' => 'in_progress',
            'is_complete' => false,
            'weeks_count' => $count,
            'entered_kwh' => $enteredKwh,
            'entered_cost' => $enteredCost,
        ];
    }
}
