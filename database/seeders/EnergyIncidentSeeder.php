<?php

namespace Database\Seeders;

use App\Models\EnergyIncident;
use App\Models\Facility;
use App\Models\Maintenance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EnergyIncidentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $adminId = $admin?->id;

        $facilities = Facility::with('submeters')->take(10)->get();
        if ($facilities->isEmpty()) {
            $this->command->info('No facilities found to seed energy incidents.');
            return;
        }

        $seedIncidents = [
            [
                'facility_index' => 0,
                'category' => 'critical_usage_spike',
                'severity' => 'critical',
                'deviation_percent' => 42.85,
                'status' => 'Open',
                'source' => 'auto',
                'affected_asset' => 'Main Utility Meter',
                'detected_at' => Carbon::now()->subDays(2)->setHour(14)->setMinute(30),
                'description' => 'Unusual energy spike detected during peak hours exceeding baseline threshold by 42.8%. Immediate load verification required.',
                'probable_cause' => 'Simultaneous full-capacity operation of secondary auxiliary loads and uncalibrated feeder breaker.',
                'immediate_action' => 'Alerted facility supervisor to verify active heavy electrical loads.',
                'resolution_summary' => null,
                'preventive_recommendation' => 'Establish strict peak-load staggering protocol during afternoon hours.',
            ],
            [
                'facility_index' => 1,
                'category' => 'grounded_power_leak',
                'severity' => 'critical',
                'deviation_percent' => 31.40,
                'status' => 'Ongoing',
                'source' => 'manual',
                'affected_asset' => 'Main Distribution Panel (MDP)',
                'detected_at' => Carbon::now()->subDays(4)->setHour(9)->setMinute(15),
                'description' => 'Insulation resistance test on Main Distribution Panel showed high neutral-to-ground leakage current causing continuous power draw.',
                'probable_cause' => 'Degraded wire insulation on 3-phase feeder cable due to moisture exposure.',
                'immediate_action' => 'Dispatched CIMM Electrical Team; isolated faulty sub-circuit and initiated cable rewiring.',
                'resolution_summary' => null,
                'preventive_recommendation' => 'Conduct quarterly insulation resistance and megger testing on all main distribution boards.',
                'maintenance_link' => [
                    'issue_type' => 'Electrical - Grounded Line',
                    'trend' => 'Critical Ground Leak',
                    'maintenance_type' => 'Corrective',
                    'maintenance_status' => 'In Progress',
                    'scheduled_date' => Carbon::now()->subDays(3)->toDateString(),
                    'assigned_to' => 'Engr. Ronald Santos (CIMM Lead Electrician)',
                    'remarks' => 'Work order initiated from Energy Incident #. Re-pulling 100mm THHN feeder cables and sealing conduit junction box.',
                ],
            ],
            [
                'facility_index' => 2,
                'category' => 'meter_defect_discrepancy',
                'severity' => 'high',
                'deviation_percent' => 24.10,
                'status' => 'Open',
                'source' => 'manual',
                'submeter_preferred' => true,
                'affected_asset' => null, // Dynamic submeter or Main Utility Meter
                'detected_at' => Carbon::now()->subDays(1)->setHour(16)->setMinute(45),
                'description' => 'Sub-meter total summation deviates from Main Utility Meter by >24%. Possible meter pulse multiplier drift or faulty digital sub-meter.',
                'probable_cause' => 'Digital energy meter current transformer (CT) ratio calibration offset.',
                'immediate_action' => 'Logged incident for technical calibration check.',
                'resolution_summary' => null,
                'preventive_recommendation' => 'Schedule bi-annual calibration for all electronic sub-meters.',
            ],
            [
                'facility_index' => 3,
                'category' => 'phantom_load_waste',
                'severity' => 'high',
                'deviation_percent' => 26.70,
                'status' => 'Resolved',
                'source' => 'auto',
                'affected_asset' => 'Main Utility Meter',
                'detected_at' => Carbon::now()->subDays(10)->setHour(23)->setMinute(10),
                'resolved_at' => Carbon::now()->subDays(8)->setHour(10)->setMinute(0),
                'description' => 'Abnormal base-load consumption sustained during non-operational midnight hours (11:00 PM to 4:00 AM).',
                'probable_cause' => 'Facility utilized as temporary 24/7 disaster emergency relief staging area during heavy rainfall event.',
                'immediate_action' => 'Validated operational logbooks with Barangay / Facility management office.',
                'resolution_summary' => 'Operational Event Confirmed: Authorized disaster response operations (Emergency Disaster Response / Evacuation Center). Closed incident without physical defect.',
                'preventive_recommendation' => 'Ensure facility administration submits advanced operational advisory for emergency activations.',
            ],
            [
                'facility_index' => 4,
                'category' => 'circuit_overload',
                'severity' => 'critical',
                'deviation_percent' => 38.90,
                'status' => 'Resolved',
                'source' => 'manual',
                'affected_asset' => 'Main Distribution Panel (MDP)',
                'detected_at' => Carbon::now()->subDays(15)->setHour(11)->setMinute(20),
                'resolved_at' => Carbon::now()->subDays(12)->setHour(17)->setMinute(30),
                'description' => 'Circuit breaker tripped repeatedly on Phase B due to unbalanced high-load distribution.',
                'probable_cause' => 'Multiple high-draw equipment connected to a single phase branch circuit.',
                'immediate_action' => 'Re-balanced load across 3-phase busbars and replaced worn 100A circuit breaker.',
                'resolution_summary' => 'CIMM electricians completed phase load balancing and verified stable ampacity reading across all phases.',
                'preventive_recommendation' => 'Enforce single-line diagram load scheduling before connecting temporary event loads.',
                'maintenance_link' => [
                    'issue_type' => 'Electrical - Circuit Overload',
                    'trend' => 'Tripped Breaker / Phase Overload',
                    'maintenance_type' => 'Corrective',
                    'maintenance_status' => 'Completed',
                    'scheduled_date' => Carbon::now()->subDays(14)->toDateString(),
                    'completed_date' => Carbon::now()->subDays(12)->toDateString(),
                    'assigned_to' => 'Electrician Marco Dela Cruz (CIMM)',
                    'remarks' => 'Replaced 100A molded case circuit breaker and re-balanced Phase B branch loads. Verified zero voltage drop.',
                ],
            ],
            [
                'facility_index' => 5,
                'category' => 'peak_demand_surge',
                'severity' => 'critical',
                'deviation_percent' => 28.30,
                'status' => 'Open',
                'source' => 'auto',
                'affected_asset' => 'Main Utility Meter',
                'detected_at' => Carbon::now()->subHours(6),
                'description' => 'Peak demand surged to 128% of historical contracted capacity during synchronous equipment startup.',
                'probable_cause' => 'Lack of staggered startup timers on heavy electrical motor drives.',
                'immediate_action' => 'Notified facility operations to monitor main breaker temperature.',
                'resolution_summary' => null,
                'preventive_recommendation' => 'Install sequential soft-starters or time-delay relays on heavy motorized equipment.',
            ],
            [
                'facility_index' => 6,
                'category' => 'abnormal_consumption_drop',
                'severity' => 'high',
                'deviation_percent' => -65.20,
                'status' => 'Ongoing',
                'source' => 'auto',
                'affected_asset' => 'Main Utility Meter',
                'detected_at' => Carbon::now()->subDays(3)->setHour(8)->setMinute(0),
                'description' => 'Energy consumption recorded a 65% drop compared to expected operational baseline during normal working hours.',
                'probable_cause' => 'Potential utility meter optical pulse sensor failure or disconnected voltage sensing lead.',
                'immediate_action' => 'Dispatched technician to perform on-site meter reading verification.',
                'resolution_summary' => null,
                'preventive_recommendation' => 'Request utility provider meter integrity inspection.',
                'maintenance_link' => [
                    'issue_type' => 'Abnormal Consumption Drop / Meter Stoppage',
                    'trend' => 'Potential Meter Failure',
                    'maintenance_type' => 'Corrective',
                    'maintenance_status' => 'In Progress',
                    'scheduled_date' => Carbon::now()->subDays(2)->toDateString(),
                    'assigned_to' => 'Technician Alex Navarro (CIMM)',
                    'remarks' => 'Coordinating with electric utility (Meralco) for revenue meter inspection and voltage lead check.',
                ],
            ],
        ];

        foreach ($seedIncidents as $data) {
            $facility = $facilities[$data['facility_index'] % $facilities->count()];
            $detectedAt = $data['detected_at'];

            $affectedAsset = $data['affected_asset'] ?? 'Main Utility Meter';
            if (!empty($data['submeter_preferred']) && $facility->submeters->isNotEmpty()) {
                $affectedAsset = 'Sub-meter: ' . $facility->submeters->first()->submeter_name;
            }

            $incident = EnergyIncident::create([
                'facility_id' => $facility->id,
                'month' => (int) $detectedAt->format('n'),
                'year' => (int) $detectedAt->format('Y'),
                'deviation_percent' => $data['deviation_percent'],
                'category' => $data['category'],
                'source' => $data['source'],
                'affected_asset' => $affectedAsset,
                'detected_at' => $detectedAt,
                'date_detected' => $detectedAt->toDateString(),
                'severity' => $data['severity'],
                'description' => $data['description'],
                'probable_cause' => $data['probable_cause'],
                'immediate_action' => $data['immediate_action'],
                'resolution_summary' => $data['resolution_summary'],
                'preventive_recommendation' => $data['preventive_recommendation'],
                'status' => $data['status'],
                'created_by' => $adminId,
                'resolved_at' => $data['resolved_at'] ?? null,
            ]);

            if (!empty($data['maintenance_link'])) {
                $maint = $data['maintenance_link'];
                Maintenance::create([
                    'facility_id' => $facility->id,
                    'energy_incident_id' => $incident->id,
                    'issue_type' => $maint['issue_type'],
                    'trigger_month' => $detectedAt->format('M Y'),
                    'trend' => $maint['trend'],
                    'maintenance_type' => $maint['maintenance_type'],
                    'maintenance_status' => $maint['maintenance_status'],
                    'scheduled_date' => $maint['scheduled_date'] ?? null,
                    'completed_date' => $maint['completed_date'] ?? null,
                    'assigned_to' => $maint['assigned_to'] ?? null,
                    'remarks' => str_replace('#', '#' . $incident->id, $maint['remarks']),
                ]);
            }
        }

        $this->command->info('Successfully seeded ' . count($seedIncidents) . ' realistic Energy Incident records!');
    }
}
