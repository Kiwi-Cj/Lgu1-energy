<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Facility;
use App\Models\EnergyProfile;
use App\Models\EnergyRecord;

class EnergyDemoSeeder extends Seeder
{
    public function run()
    {
        // Create demo facilities if none exist
        if (Facility::count() == 0) {
            Facility::factory()->count(2)->create();
        }
        $facilities = Facility::all();
        foreach ($facilities as $facility) {
            // Create an energy profile
            $profile = EnergyProfile::create([
                'facility_id' => $facility->id,
                'electric_meter_no' => 'EM-' . rand(1000, 9999),
                'utility_provider' => 'Meralco',
                'contract_account_no' => 'CA-' . rand(10000, 99999),
                'baseline_kwh' => rand(800, 1200),
                'main_energy_source' => 'Meralco',
                'backup_power' => 'Generator',
                'number_of_meters' => rand(1, 3),
            ]);
            // Create 6 months of energy records
            for ($i = 1; $i <= 6; $i++) {
                $month = $i;
                $year = 2025;
                $kwh = rand(700, 1400);
                EnergyRecord::create([
                    'facility_id' => $facility->id,
                    'month' => $month,
                    'year' => $year,
                    'kwh_consumed' => $kwh,
                    'status' => 'OK',
                ]);
            }
        }
    }
}
