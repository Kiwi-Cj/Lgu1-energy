<?php

namespace Database\Seeders;

use App\Models\EnergyProfile;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\MainMeterReading;
use App\Models\Submeter;
use App\Models\SubmeterReading;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LguFacilityMeterDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->clearMeterData();

        foreach ($this->facilityData() as $index => $data) {
            $facility = Facility::withTrashed()->firstOrNew(['name' => $data['name']]);
            $facility->fill([
                    'type' => $data['type'],
                    'department' => $data['department'],
                    'address' => $data['address'],
                    'barangay' => $data['barangay'],
                    'floor_area' => $data['floor_area'],
                    'floor_area_sqm' => $data['floor_area'],
                    'floors' => $data['floors'],
                    'year_built' => $data['year_built'],
                    'operating_hours' => $data['operating_hours'],
                    'status' => 'Active',
                    'baseline_status' => 'active',
                    'baseline_kwh' => $data['baseline_kwh'],
                    'baseline_start_date' => now()->subMonths(6)->startOfMonth()->toDateString(),
                    'engineer_approved' => true,
                ]);
            $facility->deleted_at = null;
            $facility->deleted_by = null;
            $facility->archive_reason = null;
            $facility->save();

            $mainMeter = FacilityMeter::create([
                'facility_id' => $facility->id,
                'meter_name' => $data['main_meter']['name'],
                'meter_number' => $data['main_meter']['number'],
                'meter_type' => 'main',
                'location' => $data['main_meter']['location'],
                'status' => 'active',
                'multiplier' => 1,
                'baseline_kwh' => $data['baseline_kwh'],
                'notes' => 'Demo main meter for system integration testing.',
                'approved_at' => now(),
            ]);

            EnergyProfile::create([
                'facility_id' => $facility->id,
                'primary_meter_id' => $mainMeter->id,
                'electric_meter_no' => $data['main_meter']['number'],
                'utility_provider' => 'Meralco',
                'contract_account_no' => $data['contract_account_no'],
                'baseline_kwh' => $data['baseline_kwh'],
                'main_energy_source' => 'Grid Electricity',
                'backup_power' => $data['backup_power'],
                'transformer_capacity' => $data['transformer_capacity'],
                'number_of_meters' => 3,
                'baseline_source' => 'Demo seed baseline',
            ]);

            $this->seedMainMeterReadings($facility, $mainMeter, $data, $index);
            $this->seedSubmeters($facility, $mainMeter, $data, $index);
        }
    }

    private function clearMeterData(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ([
            'main_meter_alerts',
            'main_meter_baselines',
            'main_meter_readings',
            'submeter_alerts',
            'submeter_baselines',
            'submeter_equipment_files',
            'submeter_equipments',
            'submeter_readings',
            'submeters',
            'energy_profiles',
        'facility_meters',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        if (Schema::hasTable('energy_records')) {
            EnergyRecord::withTrashed()->forceDelete();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function seedMainMeterReadings(
        Facility $facility,
        FacilityMeter $mainMeter,
        array $data,
        int $facilityIndex
    ): void {
        $recordedBy = (int) (DB::table('users')->value('id') ?? 1);
        $meterStart = $data['main_meter']['starting_kwh'];
        $monthlyKwh = $data['main_monthly_kwh'];
        $months = collect(range(5, 0))->map(fn ($offset) => now()->subMonthsNoOverflow($offset)->startOfMonth());

        foreach ($months as $i => $month) {
            $used = $monthlyKwh[$i];
            $readingStart = $meterStart;
            $readingEnd = round($readingStart + $used, 2);
            $periodEnd = $month->copy()->endOfMonth();

            $reading = MainMeterReading::create([
                'facility_id' => $facility->id,
                'period_type' => 'monthly',
                'period_start_date' => $month->toDateString(),
                'period_end_date' => $periodEnd->toDateString(),
                'reading_start_kwh' => $readingStart,
                'reading_end_kwh' => $readingEnd,
                'operating_days' => $month->daysInMonth,
                'peak_demand_kw' => round($used / ($month->daysInMonth * 24 * 0.58), 2),
                'power_factor' => 0.95,
                'input_source' => 'manual',
                'device_id' => null,
                'received_at' => null,
                'approved_at' => now(),
            ]);

            EnergyRecord::create([
                'facility_id' => $facility->id,
                'meter_id' => $mainMeter->id,
                'year' => (int) $periodEnd->format('Y'),
                'month' => (int) $periodEnd->format('n'),
                'day' => (int) $periodEnd->format('j'),
                'actual_kwh' => $used,
                'baseline_kwh' => $data['baseline_kwh'],
                'deviation' => EnergyRecord::calculateDeviation($used, $data['baseline_kwh']),
                'rate_per_kwh' => 12.35,
                'energy_cost' => round($used * 12.35, 2),
                'recorded_by' => $recordedBy,
                'alert' => EnergyRecord::resolveAlertLevel(
                    EnergyRecord::calculateDeviation($used, $data['baseline_kwh']),
                    $data['baseline_kwh']
                ),
            ]);

            $meterStart = $readingEnd;
        }
    }

    private function seedSubmeters(
        Facility $facility,
        FacilityMeter $mainMeter,
        array $data,
        int $facilityIndex
    ): void {
        foreach ($data['submeters'] as $subIndex => $submeterData) {
            $subFacilityMeter = FacilityMeter::create([
                'facility_id' => $facility->id,
                'meter_name' => $submeterData['name'],
                'meter_number' => $submeterData['meter_number'],
                'meter_type' => 'sub',
                'parent_meter_id' => $mainMeter->id,
                'location' => $submeterData['location'],
                'status' => 'active',
                'multiplier' => 1,
                'baseline_kwh' => $submeterData['baseline_kwh'],
                'notes' => 'Demo submeter under ' . $mainMeter->meter_name,
                'approved_at' => now(),
            ]);

            $submeter = Submeter::create([
                'facility_id' => $facility->id,
                'submeter_name' => $submeterData['name'],
                'meter_type' => $submeterData['meter_type'],
                'status' => 'active',
            ]);

            $readingStart = $submeterData['starting_kwh'];
            $months = collect(range(5, 0))->map(fn ($offset) => now()->subMonthsNoOverflow($offset)->startOfMonth());

            foreach ($months as $i => $month) {
                $used = $submeterData['monthly_kwh'][$i];
                $readingEnd = round($readingStart + $used, 2);
                $periodEnd = $month->copy()->endOfMonth();

                $reading = SubmeterReading::create([
                    'submeter_id' => $submeter->id,
                    'period_type' => 'monthly',
                    'period_start_date' => $month->toDateString(),
                    'period_end_date' => $periodEnd->toDateString(),
                    'reading_start_kwh' => $readingStart,
                    'reading_end_kwh' => $readingEnd,
                    'operating_days' => $month->daysInMonth,
                    'input_source' => 'iot',
                    'device_id' => sprintf('LGU-SUB-%02d-%02d', $facilityIndex + 1, $subIndex + 1),
                    'received_at' => $periodEnd->copy()->setTime(8, 45),
                    'approved_at' => now(),
                ]);

                EnergyRecord::create([
                    'facility_id' => $facility->id,
                    'meter_id' => $subFacilityMeter->id,
                    'year' => (int) $periodEnd->format('Y'),
                    'month' => (int) $periodEnd->format('n'),
                    'day' => (int) $periodEnd->format('j'),
                    'actual_kwh' => $used,
                    'baseline_kwh' => $submeterData['baseline_kwh'],
                    'deviation' => EnergyRecord::calculateDeviation($used, $submeterData['baseline_kwh']),
                    'rate_per_kwh' => 12.35,
                    'energy_cost' => round($used * 12.35, 2),
                    'recorded_by' => (int) (DB::table('users')->value('id') ?? 1),
                    'alert' => EnergyRecord::resolveAlertLevel(
                        EnergyRecord::calculateDeviation($used, $submeterData['baseline_kwh']),
                        $submeterData['baseline_kwh']
                    ),
                ]);

                $readingStart = $readingEnd;
            }
        }
    }

    private function facilityData(): array
    {
        return [
            [
                'name' => 'LGU City Hall Main Building',
                'type' => 'Government Office',
                'department' => 'City Administration',
                'address' => 'City Hall Complex, Poblacion',
                'barangay' => 'Poblacion',
                'floor_area' => 2850,
                'floors' => 4,
                'year_built' => 2012,
                'operating_hours' => '8:00 AM - 5:00 PM',
                'baseline_kwh' => 8450,
                'contract_account_no' => 'LGU-CH-2026-001',
                'backup_power' => 'Standby generator',
                'transformer_capacity' => '150 kVA',
                'main_meter' => [
                    'name' => 'City Hall Main Meter',
                    'number' => 'MAIN-LGU-CH-001',
                    'location' => 'Ground Floor Electrical Room',
                    'starting_kwh' => 125000,
                ],
                'main_monthly_kwh' => [7900, 8120, 8350, 8485, 8615, 8720],
                'submeters' => [
                    [
                        'name' => 'Administration Office Submeter',
                        'meter_number' => 'SUB-LGU-CH-ADM',
                        'location' => '2F Admin Office Panel',
                        'meter_type' => 'three_phase',
                        'baseline_kwh' => 2850,
                        'starting_kwh' => 36000,
                        'monthly_kwh' => [2650, 2740, 2810, 2865, 2920, 2985],
                    ],
                    [
                        'name' => 'Public Service Hall Submeter',
                        'meter_number' => 'SUB-LGU-CH-PSH',
                        'location' => 'Ground Floor Service Hall',
                        'meter_type' => 'three_phase',
                        'baseline_kwh' => 3350,
                        'starting_kwh' => 42000,
                        'monthly_kwh' => [3160, 3225, 3340, 3415, 3490, 3565],
                    ],
                ],
            ],
            [
                'name' => 'LGU Health Office',
                'type' => 'Health Facility',
                'department' => 'City Health Department',
                'address' => 'Health Office Compound, Barangay Central',
                'barangay' => 'Central',
                'floor_area' => 1640,
                'floors' => 2,
                'year_built' => 2018,
                'operating_hours' => '7:00 AM - 6:00 PM',
                'baseline_kwh' => 6120,
                'contract_account_no' => 'LGU-HO-2026-002',
                'backup_power' => 'UPS and standby generator',
                'transformer_capacity' => '100 kVA',
                'main_meter' => [
                    'name' => 'Health Office Main Meter',
                    'number' => 'MAIN-LGU-HO-001',
                    'location' => 'Clinic Electrical Room',
                    'starting_kwh' => 88000,
                ],
                'main_monthly_kwh' => [5750, 5895, 6030, 6180, 6325, 6460],
                'submeters' => [
                    [
                        'name' => 'Clinic and Consultation Submeter',
                        'meter_number' => 'SUB-LGU-HO-CLN',
                        'location' => 'Clinic Wing Panel',
                        'meter_type' => 'three_phase',
                        'baseline_kwh' => 2450,
                        'starting_kwh' => 27000,
                        'monthly_kwh' => [2310, 2385, 2460, 2525, 2580, 2655],
                    ],
                    [
                        'name' => 'Cold Storage and Lab Submeter',
                        'meter_number' => 'SUB-LGU-HO-LAB',
                        'location' => 'Laboratory Panel',
                        'meter_type' => 'single_phase',
                        'baseline_kwh' => 1960,
                        'starting_kwh' => 21800,
                        'monthly_kwh' => [1840, 1905, 1975, 2040, 2115, 2190],
                    ],
                ],
            ],
            [
                'name' => 'LGU Public Market',
                'type' => 'Commercial Facility',
                'department' => 'Market Administration',
                'address' => 'Public Market Complex, Barangay Central',
                'barangay' => 'Central',
                'floor_area' => 2980,
                'floors' => 2,
                'year_built' => 2016,
                'operating_hours' => '4:00 AM - 8:00 PM',
                'baseline_kwh' => 7420,
                'contract_account_no' => 'LGU-PM-2026-003',
                'backup_power' => 'Standby generator',
                'transformer_capacity' => '125 kVA',
                'main_meter' => [
                    'name' => 'Public Market Main Meter',
                    'number' => 'MAIN-LGU-PM-001',
                    'location' => 'Market Admin Electrical Room',
                    'starting_kwh' => 132000,
                ],
                'main_monthly_kwh' => [7010, 7165, 7320, 7485, 7655, 7830],
                'submeters' => [
                    [
                        'name' => 'Stalls and Common Areas Submeter',
                        'meter_number' => 'SUB-LGU-PM-STA',
                        'location' => 'Market Main Panel',
                        'meter_type' => 'three_phase',
                        'baseline_kwh' => 4120,
                        'starting_kwh' => 50100,
                        'monthly_kwh' => [3860, 3945, 4025, 4120, 4215, 4320],
                    ],
                    [
                        'name' => 'Cold Storage Submeter',
                        'meter_number' => 'SUB-LGU-PM-COLD',
                        'location' => 'Cold Storage Room',
                        'meter_type' => 'single_phase',
                        'baseline_kwh' => 1680,
                        'starting_kwh' => 18700,
                        'monthly_kwh' => [1585, 1620, 1665, 1715, 1760, 1815],
                    ],
                ],
            ],
            [
                'name' => 'LGU Engineering Office',
                'type' => 'Government Office',
                'department' => 'Engineering Department',
                'address' => 'Engineering Complex, Barangay North',
                'barangay' => 'North',
                'floor_area' => 2240,
                'floors' => 3,
                'year_built' => 2014,
                'operating_hours' => '8:00 AM - 5:00 PM',
                'baseline_kwh' => 5580,
                'contract_account_no' => 'LGU-EN-2026-004',
                'backup_power' => 'UPS backup',
                'transformer_capacity' => '75 kVA',
                'main_meter' => [
                    'name' => 'Engineering Office Main Meter',
                    'number' => 'MAIN-LGU-EN-001',
                    'location' => 'Engineering Electrical Room',
                    'starting_kwh' => 96000,
                ],
                'main_monthly_kwh' => [5480, 5560, 5650, 6410, 7025, 6840],
                'submeters' => [
                    [
                        'name' => 'Plans and Design Submeter',
                        'meter_number' => 'SUB-LGU-EN-DES',
                        'location' => 'Design Office Panel',
                        'meter_type' => 'three_phase',
                        'baseline_kwh' => 2300,
                        'starting_kwh' => 27100,
                        'monthly_kwh' => [2200, 2245, 2305, 2555, 2720, 2655],
                    ],
                    [
                        'name' => 'Field Operations Submeter',
                        'meter_number' => 'SUB-LGU-EN-FLD',
                        'location' => 'Field Operations Panel',
                        'meter_type' => 'three_phase',
                        'baseline_kwh' => 1850,
                        'starting_kwh' => 21900,
                        'monthly_kwh' => [1790, 1825, 1870, 2250, 2440, 2360],
                    ],
                ],
            ],
        ];
    }
}
