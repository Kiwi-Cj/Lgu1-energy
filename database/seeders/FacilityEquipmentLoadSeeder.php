<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\SubmeterEquipment;
use Illuminate\Database\Seeder;

class FacilityEquipmentLoadSeeder extends Seeder
{
    public function run(): void
    {
        $facilities = Facility::all();

        foreach ($facilities as $facility) {
            // If already has equipment, skip
            if (SubmeterEquipment::where('facility_id', $facility->id)->exists()) {
                continue;
            }

            $mainMeter = FacilityMeter::where('facility_id', $facility->id)
                ->where('meter_type', 'main')
                ->first();

            $mainMeterId = $mainMeter ? $mainMeter->id : null;

            if (str_contains(strtolower($facility->name), 'police')) {
                // Police Station loads
                $sampleLoads = [
                    [
                        'name' => 'Inverter Split AC 1.5HP (Investigation Office)',
                        'category' => 'HVAC / Cooling',
                        'location' => 'Investigation & Patrol Office',
                        'quantity' => 2,
                        'rated_watts' => 1150,
                        'hours_per_day' => 16,
                        'days_per_month' => 30,
                        'notes' => '24/7 desk coverage, high duty cycle',
                    ],
                    [
                        'name' => 'Window AC 1.0HP (Chief Office)',
                        'category' => 'HVAC / Cooling',
                        'location' => 'Chief of Police Room',
                        'quantity' => 1,
                        'rated_watts' => 850,
                        'hours_per_day' => 8,
                        'days_per_month' => 22,
                        'notes' => 'Office hours only',
                    ],
                    [
                        'name' => 'Desktop Computer Workstations',
                        'category' => 'IT & Office Equipment',
                        'location' => 'Records & Admin Division',
                        'quantity' => 6,
                        'rated_watts' => 180,
                        'hours_per_day' => 12,
                        'days_per_month' => 30,
                        'notes' => 'Police blotter and records encoding',
                    ],
                    [
                        'name' => 'CCTV Surveillance Server & Monitors',
                        'category' => 'IT & Office Equipment',
                        'location' => 'Command Center',
                        'quantity' => 1,
                        'rated_watts' => 450,
                        'hours_per_day' => 24,
                        'days_per_month' => 30,
                        'notes' => 'Continuous 24/7 surveillance monitoring',
                    ],
                    [
                        'name' => 'LED T8 18W Tube Fixtures (Double)',
                        'category' => 'Lighting',
                        'location' => 'Hallways & Offices',
                        'quantity' => 14,
                        'rated_watts' => 36,
                        'hours_per_day' => 14,
                        'days_per_month' => 30,
                        'notes' => 'Energy efficient LED retrofits',
                    ],
                    [
                        'name' => 'Hot & Cold Water Dispenser',
                        'category' => 'Appliances & Pantry',
                        'location' => 'Station Pantry',
                        'quantity' => 1,
                        'rated_watts' => 500,
                        'hours_per_day' => 12,
                        'days_per_month' => 30,
                        'notes' => 'Personnel pantry area',
                    ],
                ];
            } elseif (str_contains(strtolower($facility->name), 'munisipyo')) {
                // Munisipyo loads
                $sampleLoads = [
                    [
                        'name' => 'Floor Standing Inverter AC 3.0HP',
                        'category' => 'HVAC / Cooling',
                        'location' => 'Ground Floor Lobby & Treasury',
                        'quantity' => 2,
                        'rated_watts' => 2800,
                        'hours_per_day' => 8,
                        'days_per_month' => 22,
                        'notes' => 'Public transaction area',
                    ],
                    [
                        'name' => 'Inverter Split AC 2.0HP (Mayor & Admin)',
                        'category' => 'HVAC / Cooling',
                        'location' => '2nd Floor Executive Suite',
                        'quantity' => 3,
                        'rated_watts' => 1500,
                        'hours_per_day' => 9,
                        'days_per_month' => 22,
                        'notes' => 'Inverter multi-split system',
                    ],
                    [
                        'name' => 'Desktop Computer Workstations',
                        'category' => 'IT & Office Equipment',
                        'location' => 'Treasury, Assessor & Admin Offices',
                        'quantity' => 18,
                        'rated_watts' => 200,
                        'hours_per_day' => 8,
                        'days_per_month' => 22,
                        'notes' => 'Gov office workstations with dual display',
                    ],
                    [
                        'name' => 'Department Network Server Rack',
                        'category' => 'IT & Office Equipment',
                        'location' => 'MIS / Server Room',
                        'quantity' => 1,
                        'rated_watts' => 750,
                        'hours_per_day' => 24,
                        'days_per_month' => 30,
                        'notes' => 'City database server & networking switch',
                    ],
                    [
                        'name' => 'Heavy Duty Multi-Function Network Copier',
                        'category' => 'IT & Office Equipment',
                        'location' => 'Admin & Records Dept',
                        'quantity' => 3,
                        'rated_watts' => 350,
                        'hours_per_day' => 6,
                        'days_per_month' => 22,
                        'notes' => 'Department copy & scan center',
                    ],
                    [
                        'name' => 'LED Ceiling Troffer Panels 36W',
                        'category' => 'Lighting',
                        'location' => 'All Departments & Lobby',
                        'quantity' => 32,
                        'rated_watts' => 36,
                        'hours_per_day' => 9,
                        'days_per_month' => 22,
                        'notes' => 'High lumen LED office panels',
                    ],
                    [
                        'name' => 'Water Booster Pump 2.0HP',
                        'category' => 'Pumps & Motors',
                        'location' => 'Ground Floor Utility Tank',
                        'quantity' => 1,
                        'rated_watts' => 1500,
                        'hours_per_day' => 4,
                        'days_per_month' => 30,
                        'notes' => 'Building water pressurization',
                    ],
                ];
            } else {
                // Large / Multi-meter Facility
                $sampleLoads = [
                    [
                        'name' => 'Central Chiller / VRF System 10HP',
                        'category' => 'HVAC / Cooling',
                        'location' => 'Main Wing HVAC Plant',
                        'quantity' => 2,
                        'rated_watts' => 7500,
                        'hours_per_day' => 10,
                        'days_per_month' => 24,
                        'notes' => 'Variable Refrigerant Flow central cooling',
                    ],
                    [
                        'name' => 'Passenger Elevator Traction Motor 15HP',
                        'category' => 'Pumps & Motors',
                        'location' => 'Elevator Shaft A & B',
                        'quantity' => 2,
                        'rated_watts' => 11000,
                        'hours_per_day' => 6,
                        'days_per_month' => 26,
                        'notes' => 'Regenerative drive elevator',
                    ],
                    [
                        'name' => 'Enterprise Server Room Data Center',
                        'category' => 'IT & Office Equipment',
                        'location' => 'Data Center Level 3',
                        'quantity' => 1,
                        'rated_watts' => 3500,
                        'hours_per_day' => 24,
                        'days_per_month' => 30,
                        'notes' => 'Core data center infrastructure & UPS',
                    ],
                    [
                        'name' => 'LED Commercial High Bay / Recessed 40W',
                        'category' => 'Lighting',
                        'location' => 'Floors 1 - 4',
                        'quantity' => 80,
                        'rated_watts' => 40,
                        'hours_per_day' => 10,
                        'days_per_month' => 26,
                        'notes' => 'Architectural LED lighting grid',
                    ],
                    [
                        'name' => 'Desktop PC & Workstation Fleet',
                        'category' => 'IT & Office Equipment',
                        'location' => 'Floors 1 - 3 Operations',
                        'quantity' => 45,
                        'rated_watts' => 180,
                        'hours_per_day' => 8,
                        'days_per_month' => 22,
                        'notes' => 'Staff and engineering workstations',
                    ],
                ];
            }

            foreach ($sampleLoads as $load) {
                SubmeterEquipment::create([
                    'facility_id' => $facility->id,
                    'facility_meter_id' => $mainMeterId,
                    'meter_scope' => 'main',
                    'equipment_name' => $load['name'],
                    'category' => $load['category'],
                    'location' => $load['location'],
                    'quantity' => $load['quantity'],
                    'rated_watts' => $load['rated_watts'],
                    'operating_hours_per_day' => $load['hours_per_day'],
                    'operating_days_per_month' => $load['days_per_month'],
                    'notes' => $load['notes'],
                ]);
            }
        }
    }
}
