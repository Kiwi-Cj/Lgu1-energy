<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Facility;
use App\Models\EnergyEfficiency;

class UpdateEnergyEfficiencyFacilityIdSeeder extends Seeder
{
    public function run()
    {
        $rows = DB::table('energy_efficiency')->get();
        foreach ($rows as $row) {
            // Try to match facility string to Facility name
            $facility = Facility::where('name', $row->facility)->first();
            if ($facility) {
                DB::table('energy_efficiency')
                    ->where('id', $row->id)
                    ->update(['facility_id' => $facility->id]);
            }
        }
    }
}
