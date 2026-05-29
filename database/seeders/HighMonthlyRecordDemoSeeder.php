<?php

namespace Database\Seeders;

use App\Models\EnergyProfile;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\MainMeterReading;
use App\Services\MainMeterBaselineAlertService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HighMonthlyRecordDemoSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            [
                'facility' => 'LGU City Hall Main Building',
                'meter' => 'City Hall Main Meter',
                'meter_number' => 'MAIN-LGU-CH-001',
                'baseline_kwh' => 8450,
                'monthly' => [
                    '2026-04' => 9300,
                    '2026-05' => 10650,
                ],
            ],
            [
                'facility' => 'LGU Health Office',
                'meter' => 'Health Office Main Meter',
                'meter_number' => 'MAIN-LGU-HO-001',
                'baseline_kwh' => 6120,
                'monthly' => [
                    '2026-04' => 7000,
                    '2026-05' => 7400,
                ],
            ],
        ];

        $recordedBy = (int) (DB::table('users')->value('id') ?? 1);
        $baselineService = app(MainMeterBaselineAlertService::class);

        foreach ($records as $demo) {
            $facility = Facility::query()->firstOrCreate(
                ['name' => $demo['facility']],
                [
                    'type' => 'Government Facility',
                    'status' => 'Active',
                    'baseline_kwh' => $demo['baseline_kwh'],
                ]
            );

            $facility->forceFill([
                'baseline_kwh' => $demo['baseline_kwh'],
                'baseline_status' => 'active',
                'engineer_approved' => true,
            ])->save();

            $mainMeter = FacilityMeter::query()->firstOrCreate(
                [
                    'facility_id' => $facility->id,
                    'meter_name' => $demo['meter'],
                    'meter_type' => 'main',
                ],
                [
                    'meter_number' => $demo['meter_number'],
                    'location' => 'Electrical Room',
                    'status' => 'active',
                    'multiplier' => 1,
                    'approved_at' => now(),
                ]
            );

            $mainMeter->forceFill([
                'meter_number' => $mainMeter->meter_number ?: $demo['meter_number'],
                'status' => 'active',
                'baseline_kwh' => $demo['baseline_kwh'],
                'approved_at' => $mainMeter->approved_at ?: now(),
            ])->save();

            EnergyProfile::query()->updateOrCreate(
                [
                    'facility_id' => $facility->id,
                    'primary_meter_id' => $mainMeter->id,
                ],
                [
                    'electric_meter_no' => $mainMeter->meter_number,
                    'utility_provider' => 'Meralco',
                    'baseline_kwh' => $demo['baseline_kwh'],
                    'main_energy_source' => 'Grid Electricity',
                    'baseline_source' => 'High monthly record demo seed',
                ]
            );

            foreach ($demo['monthly'] as $monthKey => $actualKwh) {
                $month = Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth();
                $periodEnd = $month->copy()->endOfMonth();
                $baseline = (float) $demo['baseline_kwh'];
                $actual = (float) $actualKwh;
                $deviation = EnergyRecord::calculateDeviation($actual, $baseline);
                $alert = EnergyRecord::resolveAlertLevel($deviation, $baseline);

                $record = EnergyRecord::withTrashed()
                    ->where('facility_id', $facility->id)
                    ->where('meter_id', $mainMeter->id)
                    ->where('year', (int) $periodEnd->format('Y'))
                    ->where('month', (int) $periodEnd->format('n'))
                    ->first();

                if (! $record) {
                    $record = new EnergyRecord();
                } elseif (method_exists($record, 'trashed') && $record->trashed()) {
                    $record->restore();
                }

                $record->fill([
                    'facility_id' => $facility->id,
                    'meter_id' => $mainMeter->id,
                    'year' => (int) $periodEnd->format('Y'),
                    'month' => (int) $periodEnd->format('n'),
                    'day' => (int) $periodEnd->format('j'),
                    'actual_kwh' => $actual,
                    'baseline_kwh' => $baseline,
                    'deviation' => $deviation,
                    'rate_per_kwh' => 12.35,
                    'energy_cost' => round($actual * 12.35, 2),
                    'recorded_by' => $recordedBy,
                    'alert' => $alert,
                ]);
                $record->save();

                $previousReadingEnd = MainMeterReading::query()
                    ->where('facility_id', $facility->id)
                    ->whereDate('period_end_date', '<', $month->toDateString())
                    ->orderByDesc('period_end_date')
                    ->value('reading_end_kwh');
                $readingStart = is_numeric($previousReadingEnd) ? (float) $previousReadingEnd : 100000.0;

                $reading = MainMeterReading::query()->updateOrCreate(
                    [
                        'facility_id' => $facility->id,
                        'period_type' => 'monthly',
                        'period_start_date' => $month->toDateString(),
                        'period_end_date' => $periodEnd->toDateString(),
                    ],
                    [
                        'reading_start_kwh' => $readingStart,
                        'reading_end_kwh' => round($readingStart + $actual, 2),
                        'operating_days' => $month->daysInMonth,
                        'peak_demand_kw' => round($actual / ($month->daysInMonth * 24 * 0.60), 2),
                        'power_factor' => 0.95,
                        'input_source' => 'manual',
                        'device_id' => null,
                        'received_at' => null,
                        'encoded_by' => $recordedBy,
                        'approved_by' => $recordedBy,
                        'approved_at' => now(),
                    ]
                );

                $baselineService->processReading($reading->fresh(['facility']));
            }
        }
    }
}
