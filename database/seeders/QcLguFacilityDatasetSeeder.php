<?php

namespace Database\Seeders;

use App\Models\EnergyProfile;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class QcLguFacilityDatasetSeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            ['name' => 'Quezon City Hall Main Building', 'type' => 'Government Office', 'department' => 'Office of the City Administrator', 'address' => 'Quezon City Hall Compound, Elliptical Road, Quezon City', 'barangay' => 'Central', 'operating_hours' => '08:00-17:00', 'baseline' => 78000],
            ['name' => 'Quezon City General Hospital', 'type' => 'Health Facility', 'department' => 'Quezon City Health Services', 'address' => 'Seminary Road, Quezon City', 'barangay' => 'Bahay Toro', 'operating_hours' => '00:00-24:00', 'baseline' => 125000],
            ['name' => 'Novaliches District Center', 'type' => 'Government Office', 'department' => 'Novaliches District Administration', 'address' => 'Quirino Highway, Quezon City', 'barangay' => 'Novaliches Proper', 'operating_hours' => '08:00-17:00', 'baseline' => 32000],
            ['name' => 'Quezon City Public Library - Main', 'type' => 'Public Library', 'department' => 'Quezon City Public Library and Information Center', 'address' => 'Quezon City Hall Compound, Quezon City', 'barangay' => 'Central', 'operating_hours' => '08:00-18:00', 'baseline' => 18000],
            ['name' => 'Amoranto Sports Complex', 'type' => 'Sports Facility', 'department' => 'Sports Development Office', 'address' => 'Roces Avenue, Quezon City', 'barangay' => 'Paligsahan', 'operating_hours' => '06:00-22:00', 'baseline' => 45000],
            ['name' => 'Quezon City University - San Bartolome Campus', 'type' => 'Educational Facility', 'department' => 'Quezon City University', 'address' => 'Quirino Highway, Quezon City', 'barangay' => 'San Bartolome', 'operating_hours' => '07:00-21:00', 'baseline' => 62000],
            ['name' => 'QC DRRMO Operations Center', 'type' => 'Emergency Operations Facility', 'department' => 'Disaster Risk Reduction and Management Office', 'address' => 'Quezon City Hall Compound, Quezon City', 'barangay' => 'Central', 'operating_hours' => '00:00-24:00', 'baseline' => 38000],
            ['name' => 'Quezon City Health Department Central Office', 'type' => 'Health Office', 'department' => 'Quezon City Health Department', 'address' => 'Quezon City Hall Compound, Quezon City', 'barangay' => 'Central', 'operating_hours' => '08:00-17:00', 'baseline' => 24000],
        ];

        $monthlyFactors = [0.93, 0.96, 1.01, 1.04, 1.08, 1.02, 0.98];
        $year = 2026;
        $ratePerKwh = 13.50;

        DB::transaction(function () use ($facilities, $monthlyFactors, $year, $ratePerKwh): void {
            EnergyRecord::withTrashed()
                ->where('external_source', 'qc_lgu_dataset')
                ->where('year', $year)
                ->where('month', 8)
                ->forceDelete();

            foreach ($facilities as $facilityIndex => $data) {
                $slug = Str::slug($data['name']);
                $facility = Facility::withTrashed()->updateOrCreate(
                    ['source_key' => 'qc-lgu-dataset:'.$slug],
                    [
                        'name' => $data['name'],
                        'type' => $data['type'],
                        'department' => $data['department'],
                        'address' => $data['address'],
                        'barangay' => $data['barangay'],
                        'operating_hours' => $data['operating_hours'],
                        'status' => 'active',
                        'source' => 'local',
                        'external_ref' => null,
                        'baseline_status' => 'active',
                        'baseline_kwh' => $data['baseline'],
                        'baseline_start_date' => sprintf('%d-01-01', $year),
                        'floor_area' => null,
                        'floor_area_sqm' => null,
                        'floors' => null,
                        'year_built' => null,
                    ]
                );
                if ($facility->trashed()) {
                    $facility->restore();
                }

                $meter = FacilityMeter::withTrashed()->updateOrCreate(
                    [
                        'facility_id' => $facility->id,
                        'meter_number' => 'QC-'.strtoupper(substr(hash('sha256', $slug), 0, 10)),
                    ],
                    [
                        'meter_name' => $data['name'].' Main Meter',
                        'meter_type' => 'main',
                        'location' => 'Main electrical service entrance',
                        'status' => 'active',
                        'multiplier' => 1,
                        'baseline_kwh' => $data['baseline'],
                        'approved_at' => now(),
                        'notes' => 'Illustrative QC LGU facility dataset for energy monitoring and reports.',
                    ]
                );
                if ($meter->trashed()) {
                    $meter->restore();
                }

                EnergyProfile::updateOrCreate(
                    ['facility_id' => $facility->id],
                    [
                        'primary_meter_id' => $meter->id,
                        'electric_meter_no' => $meter->meter_number,
                        'utility_provider' => 'Electric Utility Provider',
                        'contract_account_no' => 'QC-DATASET-'.strtoupper(substr(hash('sha256', $slug), 0, 8)),
                        'baseline_kwh' => $data['baseline'],
                        'baseline_source' => 'fixed',
                        'baseline_locked' => true,
                        'engineer_approved' => true,
                        'main_energy_source' => 'Grid Electricity',
                        'backup_power' => 'Not specified',
                        'number_of_meters' => 1,
                    ]
                );

                foreach ($monthlyFactors as $monthIndex => $baseFactor) {
                    $month = $monthIndex + 1;
                    $factor = $baseFactor + (($facilityIndex % 4) - 1.5) * 0.012;
                    $actualKwh = round($data['baseline'] * $factor, 2);
                    $deviation = EnergyRecord::calculateDeviation($actualKwh, (float) $data['baseline']);
                    $record = EnergyRecord::withTrashed()->firstOrNew([
                        'facility_id' => $facility->id,
                        'meter_id' => $meter->id,
                        'year' => $year,
                        'month' => $month,
                    ]);
                    $record->fill([
                        'day' => 1,
                        'actual_kwh' => $actualKwh,
                        'baseline_kwh' => $data['baseline'],
                        'rate_per_kwh' => $ratePerKwh,
                        'energy_cost' => round($actualKwh * $ratePerKwh, 2),
                        'recorded_by' => null,
                        'recorded_by_name' => 'QC Energy Management Office',
                        'input_source' => 'manual',
                        'external_source' => 'qc_lgu_dataset',
                        'external_record_id' => sprintf('QC-%s-%d-%02d', strtoupper(substr(hash('sha256', $slug), 0, 8)), $year, $month),
                        'review_status' => 'approved',
                        'reviewed_at' => now(),
                        'review_remarks' => 'Approved illustrative monthly reading for QC LGU system demonstration.',
                        'deviation' => $deviation,
                        'alert' => EnergyRecord::resolveAlertLevel($deviation, (float) $data['baseline']),
                    ]);
                    $record->saveQuietly();
                    if ($record->trashed()) {
                        $record->restoreQuietly();
                    }
                    $submittedAt = Carbon::create($year, $month, 1, 14, 30)
                        ->endOfMonth()
                        ->min(now());
                    EnergyRecord::whereKey($record->id)->update([
                        'created_at' => $submittedAt,
                        'updated_at' => $submittedAt,
                    ]);
                }
            }
        });

        $this->command?->info('QC LGU dataset ready: 8 local facilities, 8 main meters, and 56 approved monthly readings.');
    }
}
