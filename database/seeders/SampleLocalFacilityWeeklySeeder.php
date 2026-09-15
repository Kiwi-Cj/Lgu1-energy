<?php

namespace Database\Seeders;

use App\Models\EnergyProfile;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\FacilityMeterWeeklyReading;
use App\Models\SubmeterEquipment;
use App\Models\User;
use App\Services\WeeklyReadingSyncService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleLocalFacilityWeeklySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'super_admin')
            ->orWhere('role', 'admin')
            ->first() ?? User::first();

        $adminId = $admin?->id ?? 1;
        $syncService = app(WeeklyReadingSyncService::class);

        $facilitiesConfig = [
            // =========================================================================
            // FACILITY 1: QC Community Center & Multi-Purpose Hall
            // =========================================================================
            [
                'facility' => [
                    'name' => 'QC Community Center & Multi-Purpose Hall',
                    'source' => 'local',
                    'type' => 'Community Center',
                    'address' => 'Elliptical Road, Diliman, Quezon City',
                    'barangay' => 'Central',
                    'department' => 'Community Relations Office',
                    'floor_area' => 1500,
                    'floor_area_sqm' => 1500,
                    'floors' => 2,
                    'year_built' => 2020,
                    'operating_hours' => '8:00 AM - 6:00 PM',
                    'baseline_kwh' => 2000,
                    'baseline_status' => 'active',
                    'baseline_start_date' => '2026-01-01',
                    'status' => 'active',
                    'engineer_approved' => true,
                ],
                'meter' => [
                    'meter_number' => 'MAIN-QC-CC-001',
                    'meter_name' => 'Community Center Main Meter',
                    'meter_type' => 'main',
                    'location' => 'Ground Floor Main Panel',
                    'baseline_kwh' => 2000,
                    'status' => 'active',
                ],
                'profile' => [
                    'utility_provider' => 'Meralco',
                    'contract_account_no' => 'CA-QCCC-8899',
                    'electric_meter_no' => 'MAIN-QC-CC-001',
                    'main_energy_source' => 'Electricity',
                    'backup_power' => 'Solar Hybrid & Generator',
                    'transformer_capacity' => '100 kVA',
                    'number_of_meters' => 1,
                    'baseline_kwh' => 2000,
                ],
                'rate_per_kwh' => 12.00,
                'initial_dial' => 0.00,
                'monthly_patterns' => [
                    1 => [
                        1 => ['days' => '07', 'kwh' => 450.00, 'notes' => 'Normal operational week'],
                        2 => ['days' => '14', 'kwh' => 470.00, 'notes' => 'Town hall meeting hosted'],
                        3 => ['days' => '21', 'kwh' => 440.00, 'notes' => 'Regular working hours'],
                        4 => ['days' => '31', 'kwh' => 640.00, 'notes' => 'Month-end operations & events'],
                    ],
                    2 => [
                        1 => ['days' => '07', 'kwh' => 480.00, 'notes' => 'Regular weekly consumption'],
                        2 => ['days' => '14', 'kwh' => 510.00, 'notes' => 'Community medical mission load'],
                        3 => ['days' => '21', 'kwh' => 490.00, 'notes' => 'Standard lighting & HVAC'],
                        4 => ['days' => '28', 'kwh' => 520.00, 'notes' => 'Weekend youth activity'],
                    ],
                    3 => [
                        1 => ['days' => '07', 'kwh' => 470.00, 'notes' => 'Normal load profile'],
                        2 => ['days' => '14', 'kwh' => 480.00, 'notes' => 'Regular office schedule'],
                        3 => ['days' => '21', 'kwh' => 460.00, 'notes' => 'Standard energy use'],
                        4 => ['days' => '31', 'kwh' => 690.00, 'notes' => 'Hotter weather beginning'],
                    ],
                    4 => [
                        1 => ['days' => '07', 'kwh' => 550.00, 'notes' => 'Summer heatwave - heavy HVAC use'],
                        2 => ['days' => '14', 'kwh' => 570.00, 'notes' => 'Continuous aircon cooling during heatwave'],
                        3 => ['days' => '21', 'kwh' => 540.00, 'notes' => 'Full capacity sports clinic'],
                        4 => ['days' => '30', 'kwh' => 740.00, 'notes' => 'High afternoon temperatures'],
                    ],
                    5 => [
                        1 => ['days' => '07', 'kwh' => 540.00, 'notes' => 'Barangay assembly event'],
                        2 => ['days' => '14', 'kwh' => 530.00, 'notes' => 'Consultations and meetings'],
                        3 => ['days' => '21', 'kwh' => 510.00, 'notes' => 'Standard office hours'],
                        4 => ['days' => '31', 'kwh' => 720.00, 'notes' => 'Month-end youth sports league'],
                    ],
                    6 => [
                        1 => ['days' => '07', 'kwh' => 480.00, 'notes' => 'Cooler weather with onset of rainy season'],
                        2 => ['days' => '14', 'kwh' => 490.00, 'notes' => 'Regular community activities'],
                        3 => ['days' => '21', 'kwh' => 470.00, 'notes' => 'Reduced HVAC running time'],
                        4 => ['days' => '30', 'kwh' => 560.00, 'notes' => 'Normal operations'],
                    ],
                    7 => [
                        1 => ['days' => '07', 'kwh' => 450.00, 'notes' => 'Normal consumption'],
                        2 => ['days' => '14', 'kwh' => 460.00, 'notes' => 'Regular government services'],
                        3 => ['days' => '21', 'kwh' => 440.00, 'notes' => 'Routine weekly load'],
                        4 => ['days' => '31', 'kwh' => 650.00, 'notes' => 'Month-end reporting and meetings'],
                    ],
                    8 => [
                        1 => ['days' => '07', 'kwh' => 460.00, 'notes' => 'Normal Week 1 reading'],
                        2 => ['days' => '14', 'kwh' => 480.00, 'notes' => 'Mid-month community seminars'],
                        3 => ['days' => '21', 'kwh' => 470.00, 'notes' => 'Routine weekly operations'],
                        4 => ['days' => '31', 'kwh' => 640.00, 'notes' => 'End-of-month barangay activities'],
                    ],
                ],
                'equipment' => [
                    ['equipment_name' => 'Main Hall Inverter Package AC Units', 'category' => 'HVAC / Cooling', 'location' => 'Multi-Purpose Ground Floor', 'quantity' => 2, 'rated_watts' => 3800, 'operating_hours_per_day' => 8, 'operating_days_per_month' => 26, 'notes' => 'Dual inverter floor-mounted package AC units', 'meter_scope' => 'main'],
                    ['equipment_name' => 'High-Bay LED Hall Floodlights', 'category' => 'Lighting', 'location' => 'Ceiling Canopy', 'quantity' => 16, 'rated_watts' => 150, 'operating_hours_per_day' => 10, 'operating_days_per_month' => 30, 'notes' => 'Energy-efficient LED canopy lighting', 'meter_scope' => 'main'],
                    ['equipment_name' => 'Office Split-Type Airconditioner', 'category' => 'HVAC / Cooling', 'location' => 'Admin Office 2nd Floor', 'quantity' => 2, 'rated_watts' => 1800, 'operating_hours_per_day' => 9, 'operating_days_per_month' => 22, 'notes' => 'Wall-mounted inverter units', 'meter_scope' => 'main'],
                    ['equipment_name' => 'Desktop Computer Workstations', 'category' => 'IT & Office Equipment', 'location' => 'Admin Office', 'quantity' => 6, 'rated_watts' => 180, 'operating_hours_per_day' => 8, 'operating_days_per_month' => 22, 'notes' => 'Staff PCs and administrative terminals', 'meter_scope' => 'main'],
                    ['equipment_name' => 'Water Booster Pump', 'category' => 'Pumps & Motors', 'location' => 'Rear Utility Area', 'quantity' => 1, 'rated_watts' => 1100, 'operating_hours_per_day' => 5, 'operating_days_per_month' => 30, 'notes' => 'Automatic pressurized water supply pump', 'meter_scope' => 'main'],
                ],
            ],

            // =========================================================================
            // FACILITY 2: Quezon City Hall High-Rise Complex
            // =========================================================================
            [
                'facility' => [
                    'name' => 'Quezon City Hall High-Rise Complex',
                    'source' => 'local',
                    'type' => 'Government Complex',
                    'address' => 'Mayaman St. cor. Kalayaan Ave., Diliman, Quezon City',
                    'barangay' => 'Central',
                    'department' => 'City General Services Department (CGSD)',
                    'floor_area' => 18500,
                    'floor_area_sqm' => 18500,
                    'floors' => 14,
                    'year_built' => 1972,
                    'operating_hours' => '7:00 AM - 7:00 PM',
                    'baseline_kwh' => 24500,
                    'baseline_status' => 'active',
                    'baseline_start_date' => '2026-01-01',
                    'status' => 'active',
                    'engineer_approved' => true,
                ],
                'meter' => [
                    'meter_number' => 'MAIN-QC-HALL-001',
                    'meter_name' => 'QC Hall Complex Main Digital Meter',
                    'meter_type' => 'main',
                    'location' => 'Basement Main Switchgear Room',
                    'baseline_kwh' => 24500,
                    'status' => 'active',
                ],
                'profile' => [
                    'utility_provider' => 'Meralco',
                    'contract_account_no' => 'CA-QCH-0914-8832',
                    'electric_meter_no' => 'MAIN-QC-HALL-001',
                    'main_energy_source' => 'Electricity',
                    'backup_power' => 'Solar PV Array (150kWp) & 750 kVA Standby Generator',
                    'transformer_capacity' => '1,500 kVA',
                    'number_of_meters' => 1,
                    'baseline_kwh' => 24500,
                ],
                'rate_per_kwh' => 12.50,
                'initial_dial' => 125000.00,
                'monthly_patterns' => [
                    1 => [
                        1 => ['days' => '07', 'kwh' => 5800.00, 'notes' => 'New Year government session resumption'],
                        2 => ['days' => '14', 'kwh' => 5950.00, 'notes' => 'Regular executive & legislative workload'],
                        3 => ['days' => '21', 'kwh' => 5850.00, 'notes' => 'Central air handling & server rooms load'],
                        4 => ['days' => '31', 'kwh' => 6500.00, 'notes' => 'Tax season peak office operations'],
                    ],
                    2 => [
                        1 => ['days' => '07', 'kwh' => 5900.00, 'notes' => 'Standard administrative schedule'],
                        2 => ['days' => '14', 'kwh' => 6100.00, 'notes' => 'City council public hearings'],
                        3 => ['days' => '21', 'kwh' => 5800.00, 'notes' => 'Regular department operations'],
                        4 => ['days' => '28', 'kwh' => 6000.00, 'notes' => 'Month-end administrative processing'],
                    ],
                    3 => [
                        1 => ['days' => '07', 'kwh' => 6100.00, 'notes' => 'City-wide project planning conventions'],
                        2 => ['days' => '14', 'kwh' => 6300.00, 'notes' => 'Higher ambient temperatures; increased HVAC load'],
                        3 => ['days' => '21', 'kwh' => 6200.00, 'notes' => 'Auditorium seminars & trainings'],
                        4 => ['days' => '31', 'kwh' => 6600.00, 'notes' => 'End-of-quarter budget consolidation'],
                    ],
                    4 => [
                        1 => ['days' => '07', 'kwh' => 6900.00, 'notes' => 'Summer heatwave - central chillers at high load'],
                        2 => ['days' => '14', 'kwh' => 7200.00, 'notes' => 'Continuous HVAC cooling for 14-storey complex'],
                        3 => ['days' => '21', 'kwh' => 7100.00, 'notes' => 'Full occupant load & public service counters'],
                        4 => ['days' => '30', 'kwh' => 7200.00, 'notes' => 'Extended afternoon cooling cycles'],
                    ],
                    5 => [
                        1 => ['days' => '07', 'kwh' => 6700.00, 'notes' => 'High summer ambient temperatures'],
                        2 => ['days' => '14', 'kwh' => 6900.00, 'notes' => 'City government inter-agency symposium'],
                        3 => ['days' => '21', 'kwh' => 6800.00, 'notes' => 'Standard department operations'],
                        4 => ['days' => '31', 'kwh' => 7200.00, 'notes' => 'Pre-rainy season maintenance testing'],
                    ],
                    6 => [
                        1 => ['days' => '07', 'kwh' => 6000.00, 'notes' => 'Onset of southwest monsoon, chiller load eased'],
                        2 => ['days' => '14', 'kwh' => 6200.00, 'notes' => 'Regular legislative sessions'],
                        3 => ['days' => '21', 'kwh' => 6100.00, 'notes' => 'Routine office lighting & computing equipment'],
                        4 => ['days' => '30', 'kwh' => 6500.00, 'notes' => 'Mid-year operational review'],
                    ],
                    7 => [
                        1 => ['days' => '07', 'kwh' => 5900.00, 'notes' => 'Normal government operations'],
                        2 => ['days' => '14', 'kwh' => 6050.00, 'notes' => 'Standard administrative workloads'],
                        3 => ['days' => '21', 'kwh' => 5950.00, 'notes' => 'Rainy season ambient temperature reduction'],
                        4 => ['days' => '31', 'kwh' => 6400.00, 'notes' => 'Month-end departmental reporting'],
                    ],
                    8 => [
                        1 => ['days' => '07', 'kwh' => 6000.00, 'notes' => 'Quezon City Day commemorative activities'],
                        2 => ['days' => '14', 'kwh' => 6150.00, 'notes' => 'City anniversary exhibits & public programs'],
                        3 => ['days' => '21', 'kwh' => 6050.00, 'notes' => 'Routine municipal governance services'],
                        4 => ['days' => '31', 'kwh' => 6400.00, 'notes' => 'Month-end accounting & records processing'],
                    ],
                ],
                'equipment' => [
                    ['equipment_name' => 'Central Water-Cooled Chiller Plant', 'category' => 'HVAC / Cooling', 'location' => 'Roof Deck & Basement Plant', 'quantity' => 2, 'rated_watts' => 45000, 'operating_hours_per_day' => 12, 'operating_days_per_month' => 26, 'notes' => 'Primary centralized cooling system for 14 floors', 'meter_scope' => 'main'],
                    ['equipment_name' => 'Floor Air Handling Units (AHUs)', 'category' => 'HVAC / Cooling', 'location' => 'Floors 1-14 AHU Rooms', 'quantity' => 14, 'rated_watts' => 3700, 'operating_hours_per_day' => 11, 'operating_days_per_month' => 26, 'notes' => 'Air distribution units per floor', 'meter_scope' => 'main'],
                    ['equipment_name' => 'Office LED Troffer Fixtures', 'category' => 'Lighting', 'location' => 'Floors 1-14 Hallways & Offices', 'quantity' => 420, 'rated_watts' => 40, 'operating_hours_per_day' => 11, 'operating_days_per_month' => 26, 'notes' => 'Direct low-glare office LED lighting', 'meter_scope' => 'main'],
                    ['equipment_name' => 'Data Center & Server Room UPS Systems', 'category' => 'IT & Office Equipment', 'location' => '4th Floor MIS Data Center', 'quantity' => 4, 'rated_watts' => 8500, 'operating_hours_per_day' => 24, 'operating_days_per_month' => 30, 'notes' => 'Continuous municipal servers, databases & network equipment', 'meter_scope' => 'main'],
                    ['equipment_name' => 'Passenger Traction Elevators', 'category' => 'Pumps & Motors', 'location' => 'Elevator Shafts A-D', 'quantity' => 4, 'rated_watts' => 12000, 'operating_hours_per_day' => 12, 'operating_days_per_month' => 26, 'notes' => 'High-efficiency VVVF passenger elevators', 'meter_scope' => 'main'],
                    ['equipment_name' => 'Central Hydro-Pneumatic Water Booster Pumps', 'category' => 'Pumps & Motors', 'location' => 'Basement Pump Room', 'quantity' => 3, 'rated_watts' => 5500, 'operating_hours_per_day' => 16, 'operating_days_per_month' => 30, 'notes' => 'Building water supply & pressure maintenance', 'meter_scope' => 'main'],
                ],
            ],

            // =========================================================================
            // FACILITY 3: Quezon City General Hospital & Medical Center
            // =========================================================================
            [
                'facility' => [
                    'name' => 'Quezon City General Hospital & Medical Center',
                    'source' => 'local',
                    'type' => 'District Hospital',
                    'address' => 'Seminary Road, EDSA, Bahay Toro, Project 8, Quezon City',
                    'barangay' => 'Bahay Toro',
                    'department' => 'Quezon City Health Department (QCHD)',
                    'floor_area' => 9200,
                    'floor_area_sqm' => 9200,
                    'floors' => 5,
                    'year_built' => 1985,
                    'operating_hours' => '24/7 Continuous Healthcare Service',
                    'baseline_kwh' => 14800,
                    'baseline_status' => 'active',
                    'baseline_start_date' => '2026-01-01',
                    'status' => 'active',
                    'engineer_approved' => true,
                ],
                'meter' => [
                    'meter_number' => 'MAIN-QCGH-002',
                    'meter_name' => 'QCGH Main Medical Digital Meter',
                    'meter_type' => 'main',
                    'location' => 'Medical Arts Building Switchgear',
                    'baseline_kwh' => 14800,
                    'status' => 'active',
                ],
                'profile' => [
                    'utility_provider' => 'Meralco',
                    'contract_account_no' => 'CA-QCGH-4471-2099',
                    'electric_meter_no' => 'MAIN-QCGH-002',
                    'main_energy_source' => 'Electricity',
                    'backup_power' => 'Dual Redundant 500 kVA Automatic Diesel Generators',
                    'transformer_capacity' => '800 kVA',
                    'number_of_meters' => 1,
                    'baseline_kwh' => 14800,
                ],
                'rate_per_kwh' => 12.25,
                'initial_dial' => 80000.00,
                'monthly_patterns' => [
                    1 => [
                        1 => ['days' => '07', 'kwh' => 3500.00, 'notes' => '24/7 Emergency room & ICU regular load'],
                        2 => ['days' => '14', 'kwh' => 3650.00, 'notes' => 'Radiology & laboratory equipment operation'],
                        3 => ['days' => '21', 'kwh' => 3550.00, 'notes' => 'Standard inpatient ward HVAC & medical systems'],
                        4 => ['days' => '31', 'kwh' => 3900.00, 'notes' => 'Continuous sterilization & surgical suite operations'],
                    ],
                    2 => [
                        1 => ['days' => '07', 'kwh' => 3550.00, 'notes' => 'Routine 24/7 hospital power consumption'],
                        2 => ['days' => '14', 'kwh' => 3600.00, 'notes' => 'Outpatient clinic diagnostics load'],
                        3 => ['days' => '21', 'kwh' => 3550.00, 'notes' => 'Steady medical chiller and ventilation operation'],
                        4 => ['days' => '28', 'kwh' => 3700.00, 'notes' => 'Month-end autoclave & laundry facility run'],
                    ],
                    3 => [
                        1 => ['days' => '07', 'kwh' => 3650.00, 'notes' => 'Standard healthcare delivery load'],
                        2 => ['days' => '14', 'kwh' => 3750.00, 'notes' => 'Increased cooling requirements in patient wards'],
                        3 => ['days' => '21', 'kwh' => 3700.00, 'notes' => 'Continuous pharmacy cold-chain refrigeration'],
                        4 => ['days' => '31', 'kwh' => 3800.00, 'notes' => 'End-of-month clinical sanitation cycle'],
                    ],
                    4 => [
                        1 => ['days' => '07', 'kwh' => 4100.00, 'notes' => 'Extreme heat index - medical chiller continuous cooling'],
                        2 => ['days' => '14', 'kwh' => 4300.00, 'notes' => 'Isolation ward positive pressure ventilation at peak'],
                        3 => ['days' => '21', 'kwh' => 4200.00, 'notes' => 'High operating room climate control demand'],
                        4 => ['days' => '30', 'kwh' => 4200.00, 'notes' => 'Elevated ambient temperature load across 5 floors'],
                    ],
                    5 => [
                        1 => ['days' => '07', 'kwh' => 3950.00, 'notes' => 'Hospital-wide community wellness outreach'],
                        2 => ['days' => '14', 'kwh' => 4150.00, 'notes' => 'High diagnostic imaging & laboratory run time'],
                        3 => ['days' => '21', 'kwh' => 4050.00, 'notes' => 'Standard 24/7 in-patient care load'],
                        4 => ['days' => '31', 'kwh' => 4050.00, 'notes' => 'End of summer transition load'],
                    ],
                    6 => [
                        1 => ['days' => '07', 'kwh' => 3600.00, 'notes' => 'Monsoon season onset, reduced chiller strain'],
                        2 => ['days' => '14', 'kwh' => 3700.00, 'notes' => 'Routine hospital clinical operations'],
                        3 => ['days' => '21', 'kwh' => 3650.00, 'notes' => 'Regular diagnostic & therapeutic procedures'],
                        4 => ['days' => '30', 'kwh' => 3800.00, 'notes' => 'Standard medical equipment baseline'],
                    ],
                    7 => [
                        1 => ['days' => '07', 'kwh' => 3550.00, 'notes' => 'Normal weekly hospital load'],
                        2 => ['days' => '14', 'kwh' => 3650.00, 'notes' => 'Routine intensive care & surgery schedule'],
                        3 => ['days' => '21', 'kwh' => 3600.00, 'notes' => 'Laboratory and blood bank constant power'],
                        4 => ['days' => '31', 'kwh' => 3900.00, 'notes' => 'Month-end medical equipment calibration'],
                    ],
                    8 => [
                        1 => ['days' => '07', 'kwh' => 3650.00, 'notes' => 'Standard hospital operations'],
                        2 => ['days' => '14', 'kwh' => 3700.00, 'notes' => 'Pediatric and maternal health drive'],
                        3 => ['days' => '21', 'kwh' => 3650.00, 'notes' => 'Routine daily diagnostic & ward power'],
                        4 => ['days' => '31', 'kwh' => 3850.00, 'notes' => 'End-of-month healthcare operational review'],
                    ],
                ],
                'equipment' => [
                    ['equipment_name' => 'Operating Room & ICU Dedicated Cleanroom HVAC', 'category' => 'HVAC / Cooling', 'location' => '3rd Floor Surgical Complex', 'quantity' => 3, 'rated_watts' => 18000, 'operating_hours_per_day' => 24, 'operating_days_per_month' => 30, 'notes' => 'HEPA filtered constant temperature and humidity sterile air conditioning', 'meter_scope' => 'main'],
                    ['equipment_name' => 'Inpatient Wards Multi-Split Inverter ACs', 'category' => 'HVAC / Cooling', 'location' => 'Floors 2, 4, 5 Patient Rooms', 'quantity' => 36, 'rated_watts' => 1500, 'operating_hours_per_day' => 18, 'operating_days_per_month' => 30, 'notes' => 'Individual patient room climate control', 'meter_scope' => 'main'],
                    ['equipment_name' => 'CT Scan & Digital X-Ray Imaging Suite', 'category' => 'Specialized Machinery', 'location' => 'Ground Floor Radiology', 'quantity' => 2, 'rated_watts' => 15000, 'operating_hours_per_day' => 10, 'operating_days_per_month' => 30, 'notes' => 'Medical diagnostic imaging units with high instantaneous surge capacity', 'meter_scope' => 'main'],
                    ['equipment_name' => 'Central Steam Autoclave & Sterilizer Plant', 'category' => 'Specialized Machinery', 'location' => 'Central Sterile Supply Dept (CSSD)', 'quantity' => 2, 'rated_watts' => 9000, 'operating_hours_per_day' => 8, 'operating_days_per_month' => 30, 'notes' => 'Surgical instrument steam sterilization', 'meter_scope' => 'main'],
                    ['equipment_name' => 'Medical Laboratory Ultra-Low Temperature Freezers', 'category' => 'IT & Office Equipment', 'location' => 'Laboratory & Blood Bank', 'quantity' => 4, 'rated_watts' => 1200, 'operating_hours_per_day' => 24, 'operating_days_per_month' => 30, 'notes' => '-80C vaccine, biological sample, and blood plasma storage', 'meter_scope' => 'main'],
                    ['equipment_name' => 'Bed Elevator & Service Lift', 'category' => 'Pumps & Motors', 'location' => 'Hospital Core Lift Bank', 'quantity' => 2, 'rated_watts' => 11000, 'operating_hours_per_day' => 20, 'operating_days_per_month' => 30, 'notes' => 'Hospital stretcher and heavy equipment transport', 'meter_scope' => 'main'],
                ],
            ],
        ];

        foreach ($facilitiesConfig as $cfg) {
            $fData = $cfg['facility'];
            $facility = Facility::updateOrCreate(
                ['name' => $fData['name']],
                $fData
            );

            // Create/Update Main Meter
            $mData = $cfg['meter'];
            $meter = FacilityMeter::updateOrCreate(
                [
                    'facility_id' => $facility->id,
                    'meter_number' => $mData['meter_number'],
                ],
                array_merge($mData, [
                    'facility_id' => $facility->id,
                    'approved_at' => now(),
                    'approved_by_user_id' => $adminId,
                ])
            );

            // Create/Update Energy Profile
            $pData = $cfg['profile'];
            EnergyProfile::updateOrCreate(
                ['facility_id' => $facility->id],
                array_merge($pData, [
                    'primary_meter_id' => $meter->id,
                    'engineer_approved' => true,
                    'baseline_locked' => true,
                    'baseline_source' => 'manual_seed',
                ])
            );

            // Clean previous seeded weekly readings & energy records for fresh, continuous dial state
            FacilityMeterWeeklyReading::where('facility_id', $facility->id)
                ->where('meter_id', $meter->id)
                ->delete();

            DB::table('energy_records')
                ->where('facility_id', $facility->id)
                ->where('meter_id', $meter->id)
                ->where('year', 2026)
                ->delete();

            // Populate Weekly Readings
            $ratePerKwh = $cfg['rate_per_kwh'];
            $currentDial = $cfg['initial_dial'];

            foreach ($cfg['monthly_patterns'] as $month => $weeks) {
                foreach ($weeks as $wNum => $wData) {
                    $prevDial = $currentDial;
                    $kwh = (float) $wData['kwh'];
                    $currentDial = round($prevDial + $kwh, 2);
                    $cost = round($kwh * $ratePerKwh, 2);
                    $dateStr = sprintf('2026-%02d-%s', $month, $wData['days']);

                    FacilityMeterWeeklyReading::create([
                        'facility_id' => $facility->id,
                        'meter_id' => $meter->id,
                        'year' => 2026,
                        'month' => $month,
                        'week_number' => $wNum,
                        'reading_date' => $dateStr,
                        'previous_reading_kwh' => $prevDial,
                        'current_reading_kwh' => $currentDial,
                        'actual_kwh' => $kwh,
                        'rate_per_kwh' => $ratePerKwh,
                        'cost' => $cost,
                        'notes' => $wData['notes'],
                        'encoded_by' => $adminId,
                    ]);
                }

                // Trigger monthly aggregation sync
                $syncService->syncMonthlyAggregate($facility->id, $meter->id, 2026, $month);
            }

            // Populate Equipment Load Inventory
            if (!empty($cfg['equipment'])) {
                SubmeterEquipment::where('facility_id', $facility->id)->delete();
                foreach ($cfg['equipment'] as $eq) {
                    SubmeterEquipment::create(array_merge($eq, [
                        'facility_id' => $facility->id,
                        'facility_meter_id' => $meter->id,
                    ]));
                }
            }

            $this->command?->info("✓ Seeded Local Facility '{$facility->name}' (ID: {$facility->id}) with Energy Profile, Equipment Loads, and Jan-Aug 2026 Weekly & Monthly Readings.");
        }
    }
}
