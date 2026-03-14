<?php

namespace Database\Seeders;

use App\Models\EnergyProfile;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\MainMeterReading;
use App\Models\Submeter;
use App\Models\SubmeterReading;
use App\Models\User;
use App\Services\MainMeterBaselineAlertService;
use App\Services\SubmeterBaselineAlertService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class FiveFacilitiesEnergySeeder extends Seeder
{
    private array $columnCache = [];

    public function run(): void
    {
        $actor = $this->resolveActorUser();
        $months = $this->buildMonthRange('2025-02', '2026-02');

        $facilityConfigs = [
            [
                'name' => '[Seeder 5F] City Hall Annex',
                'type' => 'Government Office',
                'department' => 'Administrative Services',
                'address' => 'Rizal Street, Poblacion',
                'barangay' => 'Poblacion',
                'floor_area' => 2600,
                'submeters' => [
                    ['name' => 'Engineering Office', 'baseline_kwh' => 3200],
                    ['name' => 'Aircon Plant Room', 'baseline_kwh' => 2800],
                    ['name' => '2F Lighting', 'baseline_kwh' => 1900],
                ],
            ],
            [
                'name' => '[Seeder 5F] Public Market Complex',
                'type' => 'Commercial Building',
                'department' => 'Market Operations',
                'address' => 'Market Road, Zone 2',
                'barangay' => 'San Roque',
                'floor_area' => 3400,
                'submeters' => [
                    ['name' => 'Stall Block A', 'baseline_kwh' => 4100],
                    ['name' => 'Stall Block B', 'baseline_kwh' => 3950],
                    ['name' => 'Cold Storage', 'baseline_kwh' => 3600],
                    ['name' => 'Perimeter Lights', 'baseline_kwh' => 1450],
                ],
            ],
            [
                'name' => '[Seeder 5F] Sports Center',
                'type' => 'Sports Facility',
                'department' => 'Sports Development',
                'address' => 'Quezon Avenue, North District',
                'barangay' => 'Maligaya',
                'floor_area' => 4200,
                'submeters' => [
                    ['name' => 'Main Arena Lighting', 'baseline_kwh' => 4700],
                    ['name' => 'Gym HVAC', 'baseline_kwh' => 4300],
                    ['name' => 'Auxiliary Rooms', 'baseline_kwh' => 1650],
                ],
            ],
            [
                'name' => '[Seeder 5F] Rural Health Unit',
                'type' => 'Healthcare',
                'department' => 'Health Office',
                'address' => 'Mabini Street, East District',
                'barangay' => 'San Isidro',
                'floor_area' => 1850,
                'submeters' => [
                    ['name' => 'Laboratory Wing', 'baseline_kwh' => 2100],
                    ['name' => 'Pharmacy and Storage', 'baseline_kwh' => 1650],
                    ['name' => 'Outpatient Area', 'baseline_kwh' => 1750],
                ],
            ],
            [
                'name' => '[Seeder 5F] Library and Museum Hub',
                'type' => 'Educational/Cultural',
                'department' => 'Culture and Tourism',
                'address' => 'Bonifacio Street, West District',
                'barangay' => 'Luna',
                'floor_area' => 2300,
                'submeters' => [
                    ['name' => 'Library Hall', 'baseline_kwh' => 2300],
                    ['name' => 'Museum Gallery', 'baseline_kwh' => 2600],
                ],
            ],
        ];

        $mainRunning = [];
        $subRunning = [];
        $allMainReadingIds = [];
        $allSubReadingIds = [];

        foreach ($facilityConfigs as $facilityIndex => $facilityConfig) {
            $subBaselineSum = collect($facilityConfig['submeters'])->sum('baseline_kwh');
            $mainBaseline = round($subBaselineSum * 1.12, 2);

            $facility = $this->upsertFacility($facilityConfig, $mainBaseline);
            $this->attachUserToFacility($actor->id, (int) $facility->id);

            $mainMeter = $this->upsertMeter(
                (int) $facility->id,
                'Main Utility Meter',
                'main',
                [
                    'meter_number' => sprintf('M%02d-0001', $facilityIndex + 1),
                    'status' => 'active',
                    'location' => 'Main Electrical Room',
                    'multiplier' => 1.0,
                    'baseline_kwh' => $mainBaseline,
                    'notes' => 'Primary main meter seeded dataset',
                ]
            );

            $profilePayload = $this->filterColumns('energy_profiles', [
                'facility_id' => $facility->id,
                'primary_meter_id' => $mainMeter->id,
                'electric_meter_no' => (string) ($mainMeter->meter_number ?? ('METER-' . $mainMeter->id)),
                'utility_provider' => 'Meralco',
                'contract_account_no' => sprintf('CA-%06d', (int) $facility->id),
                'baseline_kwh' => $mainBaseline,
                'main_energy_source' => 'Electricity',
                'backup_power' => 'Generator',
                'transformer_capacity' => '100 kVA',
                'number_of_meters' => count($facilityConfig['submeters']) + 1,
                'baseline_source' => 'historical_data',
                'engineer_approved' => true,
                'baseline_locked' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
            $this->upsertEnergyProfile((int) $facility->id, $profilePayload);

            $mainStart = $mainRunning[(int) $facility->id] ?? (45000 + (($facilityIndex + 1) * 9500));
            $submeterMap = [];

            foreach ($facilityConfig['submeters'] as $subIndex => $subConfig) {
                $subMeter = $this->upsertMeter(
                    (int) $facility->id,
                    $subConfig['name'],
                    'sub',
                    [
                        'meter_number' => sprintf('S%02d-%03d', $facilityIndex + 1, $subIndex + 1),
                        'parent_meter_id' => $mainMeter->id,
                        'status' => 'active',
                        'location' => 'Panel ' . ($subIndex + 1),
                        'multiplier' => 1.0,
                        'baseline_kwh' => (float) $subConfig['baseline_kwh'],
                        'notes' => 'Submeter seeded dataset',
                    ]
                );

                $submeter = Submeter::query()->updateOrCreate(
                    [
                        'facility_id' => (int) $facility->id,
                        'submeter_name' => (string) $subConfig['name'],
                    ],
                    [
                        'meter_type' => 'single_phase',
                        'status' => 'active',
                    ]
                );

                $submeterMap[] = [
                    'sub_config' => $subConfig,
                    'facility_meter' => $subMeter,
                    'submeter' => $submeter,
                    'running_start' => $subRunning[(int) $submeter->id] ?? (9000 + (($facilityIndex + 1) * 1700) + ($subIndex * 550)),
                ];
            }

            foreach ($months as $monthIndex => $monthStart) {
                $periodStart = $monthStart->copy()->startOfMonth();
                $periodEnd = $monthStart->copy()->endOfMonth();
                $days = (int) $periodEnd->day;
                $ym = $periodStart->format('Y-m');
                $seasonal = sin(((int) $periodStart->format('n') / 12) * 2 * pi()) * 0.04;
                $trend = $monthIndex * 0.0035;
                $facilityOffset = ($facilityIndex - 2) * 0.006;
                $ratePerKwh = round(11.80 + ($facilityIndex * 0.27), 2);

                $totalSubActual = 0.0;
                foreach ($submeterMap as $subIndex => &$subEntry) {
                    $baseline = (float) $subEntry['sub_config']['baseline_kwh'];
                    $subOffset = ($subIndex - 1) * 0.008;
                    $ratio = 1 + $seasonal + $trend + $facilityOffset + $subOffset;

                    // Inject realistic high-consumption samples in late period for testing alerts/cards.
                    if ($ym === '2026-02' && in_array($facilityIndex, [1, 3], true) && $subIndex === 0) {
                        $ratio += 0.24;
                    }

                    $actualKwh = round(max(60, $baseline * $ratio), 2);
                    $totalSubActual += $actualKwh;

                    $subStart = (float) $subEntry['running_start'];
                    $subEnd = round($subStart + $actualKwh, 2);
                    $subEntry['running_start'] = $subEnd;
                    $subRunning[(int) $subEntry['submeter']->id] = $subEnd;

                    $this->upsertEnergyRecord(
                        (int) $facility->id,
                        (int) $subEntry['facility_meter']->id,
                        (int) $periodStart->format('Y'),
                        (int) $periodStart->format('n'),
                        $actor->id,
                        $actualKwh,
                        $baseline,
                        $ratePerKwh
                    );

                    $subReading = SubmeterReading::query()->updateOrCreate(
                        [
                            'submeter_id' => (int) $subEntry['submeter']->id,
                            'period_type' => 'monthly',
                            'period_start_date' => $periodStart->toDateString(),
                            'period_end_date' => $periodEnd->toDateString(),
                        ],
                        [
                            'reading_start_kwh' => $subStart,
                            'reading_end_kwh' => $subEnd,
                            'operating_days' => $days,
                            'encoded_by_user_id' => $actor->id,
                            'approved_by_engineer_id' => $actor->id,
                            'approved_at' => $periodEnd->copy()->setTime(17, 0, 0),
                        ]
                    );
                    $allSubReadingIds[] = (int) $subReading->id;
                }
                unset($subEntry);

                $baseLoad = max(180, round($mainBaseline - $subBaselineSum, 2));
                $mainActual = round($totalSubActual + ($baseLoad * (1 + $seasonal + ($trend / 2))), 2);
                if ($ym === '2026-02' && $facilityIndex === 0) {
                    $mainActual = round($mainActual * 1.18, 2);
                }

                $mainEnd = round($mainStart + $mainActual, 2);
                $averageKw = $mainActual / max(1, $days * 24);
                $peakDemand = round(($averageKw / 0.60) * 1.05, 2);
                $powerFactor = round(min(0.99, max(0.91, 0.965 - ($facilityIndex * 0.006) + (($monthIndex % 4) * 0.003))), 4);

                $this->upsertEnergyRecord(
                    (int) $facility->id,
                    (int) $mainMeter->id,
                    (int) $periodStart->format('Y'),
                    (int) $periodStart->format('n'),
                    $actor->id,
                    $mainActual,
                    $mainBaseline,
                    $ratePerKwh
                );

                $mainReading = MainMeterReading::query()->updateOrCreate(
                    [
                        'facility_id' => (int) $facility->id,
                        'period_type' => 'monthly',
                        'period_start_date' => $periodStart->toDateString(),
                        'period_end_date' => $periodEnd->toDateString(),
                    ],
                    [
                        'reading_start_kwh' => $mainStart,
                        'reading_end_kwh' => $mainEnd,
                        'operating_days' => $days,
                        'peak_demand_kw' => $peakDemand,
                        'power_factor' => $powerFactor,
                        'encoded_by' => $actor->id,
                        'approved_by' => $actor->id,
                        'approved_at' => $periodEnd->copy()->setTime(17, 0, 0),
                    ]
                );
                $allMainReadingIds[] = (int) $mainReading->id;

                $mainStart = $mainEnd;
                $mainRunning[(int) $facility->id] = $mainEnd;
            }
        }

        $this->recomputeMainBaselinesAndAlerts($allMainReadingIds);
        $this->recomputeSubmeterBaselinesAndAlerts($allSubReadingIds);
    }

    private function buildMonthRange(string $fromYm, string $toYm): array
    {
        $months = [];
        $cursor = Carbon::createFromFormat('Y-m', $fromYm)->startOfMonth();
        $end = Carbon::createFromFormat('Y-m', $toYm)->startOfMonth();
        while ($cursor->lte($end)) {
            $months[] = $cursor->copy();
            $cursor->addMonthNoOverflow();
        }

        return $months;
    }

    private function resolveActorUser(): User
    {
        $user = User::query()
            ->whereIn('role', ['super_admin', 'super admin', 'admin', 'energy_officer', 'engineer'])
            ->orderBy('id')
            ->first();

        if ($user) {
            return $user;
        }

        $now = now();
        return User::query()->create([
            'full_name' => 'Seeder Admin',
            'name' => 'Seeder Admin',
            'email' => 'seeder-admin@example.com',
            'username' => 'seederadmin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'department' => 'IT',
            'contact_number' => '09120000000',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function upsertFacility(array $config, float $baselineKwh): Facility
    {
        $payload = $this->filterColumns('facilities', [
            'name' => $config['name'],
            'type' => $config['type'] ?? 'Government Office',
            'department' => $config['department'] ?? 'Operations',
            'address' => $config['address'] ?? 'LGU Compound',
            'barangay' => $config['barangay'] ?? 'Poblacion',
            'location' => $config['barangay'] ?? 'Poblacion',
            'floor_area' => (float) ($config['floor_area'] ?? 1500),
            'floor_area_sqm' => (float) ($config['floor_area'] ?? 1500),
            'floors' => 2,
            'year_built' => 2019,
            'operating_hours' => '8AM-5PM',
            'status' => 'active',
            'baseline_kwh' => $baselineKwh,
            'baseline_status' => 'active',
            'baseline_start_date' => '2025-02-01',
            'engineer_approved' => true,
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        $facility = Facility::withTrashed()->where('name', $config['name'])->first();
        if ($facility) {
            if ($facility->trashed()) {
                $facility->restore();
            }
            $facility->forceFill(collect($payload)->except(['created_at'])->all());
            $facility->save();
            return $facility->refresh();
        }

        $id = DB::table('facilities')->insertGetId($payload);
        return Facility::query()->findOrFail($id);
    }

    private function attachUserToFacility(int $userId, int $facilityId): void
    {
        if (! Schema::hasTable('facility_user')) {
            return;
        }

        DB::table('facility_user')->updateOrInsert(
            ['user_id' => $userId, 'facility_id' => $facilityId],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    private function upsertMeter(int $facilityId, string $name, string $meterType, array $attributes): FacilityMeter
    {
        $meter = FacilityMeter::withTrashed()
            ->where('facility_id', $facilityId)
            ->where('meter_name', $name)
            ->where('meter_type', $meterType)
            ->first();

        if (! $meter) {
            $meter = new FacilityMeter();
        }

        $meter->fill(array_merge([
            'facility_id' => $facilityId,
            'meter_name' => $name,
            'meter_type' => $meterType,
        ], $attributes));

        if ($meter->trashed()) {
            $meter->restore();
        }

        $meter->deleted_by = null;
        $meter->archive_reason = null;
        $meter->save();

        return $meter->refresh();
    }

    private function upsertEnergyProfile(int $facilityId, array $payload): void
    {
        $profile = EnergyProfile::query()->where('facility_id', $facilityId)->first();
        if ($profile) {
            $profile->fill(collect($payload)->except(['facility_id', 'created_at'])->all());
            $profile->save();
            return;
        }

        DB::table('energy_profiles')->insert($payload);
    }

    private function upsertEnergyRecord(
        int $facilityId,
        int $meterId,
        int $year,
        int $month,
        int $recordedBy,
        float $actualKwh,
        float $baselineKwh,
        float $ratePerKwh
    ): void {
        $deviation = $baselineKwh > 0 ? round((($actualKwh - $baselineKwh) / $baselineKwh) * 100, 2) : null;
        $alert = $deviation === null
            ? 'Normal'
            : ($deviation > 25 ? 'Critical' : ($deviation > 15 ? 'Warning' : 'Normal'));

        $values = $this->filterColumns('energy_records', [
            'facility_id' => $facilityId,
            'meter_id' => $meterId,
            'year' => $year,
            'month' => $month,
            'day' => 1,
            'actual_kwh' => round($actualKwh, 2),
            'baseline_kwh' => round($baselineKwh, 2),
            'deviation' => $deviation,
            'alert' => $alert,
            'energy_cost' => round($actualKwh * $ratePerKwh, 2),
            'rate_per_kwh' => $ratePerKwh,
            'recorded_by' => $recordedBy,
            'updated_at' => now(),
            'created_at' => now(),
            'deleted_at' => null,
        ]);

        $query = EnergyRecord::withTrashed()
            ->where('facility_id', $facilityId)
            ->where('meter_id', $meterId)
            ->where('year', $year)
            ->where('month', $month);

        if (array_key_exists('day', $values)) {
            $query->where('day', (int) $values['day']);
        }

        $record = $query->first();
        if ($record) {
            if ($record->trashed()) {
                $record->restore();
            }
            $record->fill(collect($values)->except(['created_at'])->all());
            $record->save();
            return;
        }

        EnergyRecord::query()->create($values);
    }

    private function recomputeMainBaselinesAndAlerts(array $readingIds): void
    {
        if (empty($readingIds)) {
            return;
        }

        $service = app(MainMeterBaselineAlertService::class);
        MainMeterReading::query()
            ->whereIn('id', array_values(array_unique($readingIds)))
            ->approved()
            ->orderBy('facility_id')
            ->orderBy('period_end_date')
            ->orderBy('id')
            ->chunk(200, function ($rows) use ($service) {
                foreach ($rows as $row) {
                    $service->processReading($row);
                }
            });
    }

    private function recomputeSubmeterBaselinesAndAlerts(array $readingIds): void
    {
        if (empty($readingIds)) {
            return;
        }

        $service = app(SubmeterBaselineAlertService::class);
        SubmeterReading::query()
            ->whereIn('id', array_values(array_unique($readingIds)))
            ->approved()
            ->orderBy('submeter_id')
            ->orderBy('period_end_date')
            ->orderBy('id')
            ->chunk(200, function ($rows) use ($service) {
                foreach ($rows as $row) {
                    $service->processReading($row);
                }
            });
    }

    private function filterColumns(string $table, array $attributes): array
    {
        $columns = $this->getColumns($table);
        return array_filter(
            $attributes,
            fn ($key) => in_array($key, $columns, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    private function getColumns(string $table): array
    {
        if (! isset($this->columnCache[$table])) {
            $this->columnCache[$table] = Schema::hasTable($table)
                ? Schema::getColumnListing($table)
                : [];
        }

        return $this->columnCache[$table];
    }
}
