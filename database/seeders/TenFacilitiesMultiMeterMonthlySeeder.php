<?php

namespace Database\Seeders;

use App\Models\EnergyProfile;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class TenFacilitiesMultiMeterMonthlySeeder extends Seeder
{
    private array $columnCache = [];

    public function run(): void
    {
        $actor = $this->resolveActorUser();
        $months = $this->buildMonthRange(
            Carbon::create(2025, 1, 1)->startOfMonth(),
            now()->copy()->startOfMonth()
        );

        $facilityDefinitions = [
            ['name' => '[Seeder 10F] City Hall Annex', 'type' => 'Government Office', 'department' => 'Administrative Services', 'industry_key' => 'admin'],
            ['name' => '[Seeder 10F] Public Market Complex', 'type' => 'Commercial Building', 'department' => 'Market Operations', 'industry_key' => 'market'],
            ['name' => '[Seeder 10F] Sports Center', 'type' => 'Sports Facility', 'department' => 'Sports Development', 'industry_key' => 'sports'],
            ['name' => '[Seeder 10F] Rural Health Unit', 'type' => 'Healthcare', 'department' => 'Health Office', 'industry_key' => 'health'],
            ['name' => '[Seeder 10F] Library and Museum Hub', 'type' => 'Educational/Cultural', 'department' => 'Culture and Tourism', 'industry_key' => 'library'],
            ['name' => '[Seeder 10F] Engineering Services Building', 'type' => 'Government Office', 'department' => 'Engineering Services', 'industry_key' => 'engineering'],
            ['name' => '[Seeder 10F] Social Welfare Center', 'type' => 'Government Office', 'department' => 'Social Welfare', 'industry_key' => 'social'],
            ['name' => '[Seeder 10F] Transport Terminal', 'type' => 'Transport Hub', 'department' => 'Traffic Management', 'industry_key' => 'transport'],
            ['name' => '[Seeder 10F] Disaster Operations Center', 'type' => 'Emergency Facility', 'department' => 'Disaster Risk Reduction', 'industry_key' => 'disaster'],
            ['name' => '[Seeder 10F] Community College Campus', 'type' => 'Educational Institution', 'department' => 'Education', 'industry_key' => 'education'],
        ];

        foreach ($facilityDefinitions as $facilityIndex => $facilityDef) {
            $facilityName = (string) $facilityDef['name'];
            $industryKey = (string) $facilityDef['industry_key'];
            $mainDefinitions = $this->buildMainDefinitions($facilityIndex, $industryKey);
            $facilityBaseline = round(
                collect($mainDefinitions)->sum(fn ($mainDef) => (float) ($mainDef['baseline_kwh'] ?? 0)),
                2
            );

            $facility = $this->upsertFacility([
                'name' => $facilityName,
                'type' => (string) ($facilityDef['type'] ?? 'Government Facility'),
                'department' => (string) ($facilityDef['department'] ?? 'Operations'),
                'address' => 'LGU Compound',
                'barangay' => 'Poblacion',
                'location' => 'Poblacion',
                'floor_area' => 2200 + ($facilityIndex * 180),
                'status' => 'active',
                'baseline_kwh' => $facilityBaseline,
                'baseline_status' => 'active',
                'baseline_start_date' => '2025-01-01',
                'engineer_approved' => true,
            ]);

            $this->attachUserToFacility((int) $actor->id, (int) $facility->id);

            $mainMeterRows = [];
            foreach ($mainDefinitions as $mainDef) {
                $mainMeter = $this->upsertMeter((int) $facility->id, [
                    'meter_name' => (string) $mainDef['name'],
                    'meter_number' => (string) $mainDef['number'],
                    'meter_type' => 'main',
                    'parent_meter_id' => null,
                    'location' => 'Main Electrical Room',
                    'status' => 'active',
                    'multiplier' => 1.0,
                    'baseline_kwh' => (float) $mainDef['baseline_kwh'],
                    'notes' => 'Seeded main meter for 10-facility dataset',
                    'approved_by_user_id' => (int) $actor->id,
                    'approved_at' => now(),
                ]);

                $submeters = [];
                foreach ($mainDef['submeters'] as $subDef) {
                    $submeters[] = $this->upsertMeter((int) $facility->id, [
                        'meter_name' => (string) $subDef['name'],
                        'meter_number' => (string) $subDef['number'],
                        'meter_type' => 'sub',
                        'parent_meter_id' => (int) $mainMeter->id,
                        'location' => 'Sub Distribution Panel',
                        'status' => 'active',
                        'multiplier' => 1.0,
                        'baseline_kwh' => (float) $subDef['baseline_kwh'],
                        'notes' => 'Seeded sub meter for 10-facility dataset',
                        'approved_by_user_id' => (int) $actor->id,
                        'approved_at' => now(),
                    ]);
                }

                $mainMeterRows[] = [
                    'main_meter' => $mainMeter,
                    'baseline_kwh' => (float) $mainDef['baseline_kwh'],
                    'submeter_defs' => $mainDef['submeters'],
                    'submeters' => $submeters,
                ];
            }

            $primaryMainMeter = $mainMeterRows[0]['main_meter'] ?? null;
            if ($primaryMainMeter) {
                $this->upsertEnergyProfile(
                    (int) $facility->id,
                    (int) $primaryMainMeter->id,
                    (int) count($mainMeterRows),
                    (int) collect($mainMeterRows)->sum(fn ($row) => count($row['submeters'])),
                    (float) $facilityBaseline
                );
            }

            foreach ($months as $monthIndex => $monthStart) {
                $year = (int) $monthStart->year;
                $month = (int) $monthStart->month;
                $trend = $monthIndex * 0.0018;
                $ratePerKwh = $this->resolveTariffRatePerKwh($year, $month, $facilityIndex);
                $facilityOutageFactor = $this->resolveFacilityOutageFactor($facilityIndex, $year, $month);

                foreach ($mainMeterRows as $mainIdx => $mainRow) {
                    $mainMeter = $mainRow['main_meter'];
                    $mainBaseline = (float) $mainRow['baseline_kwh'];
                    $subTotal = 0.0;
                    $mainMaintenanceFactor = $this->resolveMainMaintenanceFactor($facilityIndex, $mainIdx, $year, $month);

                    foreach ($mainRow['submeters'] as $subIdx => $subMeter) {
                        $subBaseline = (float) ($mainRow['submeter_defs'][$subIdx]['baseline_kwh'] ?? 0);
                        $subProfile = (string) ($mainRow['submeter_defs'][$subIdx]['profile'] ?? 'mixed');
                        $noise = $this->deterministicNoise($facilityIndex, $mainIdx, $subIdx, $year, $month);

                        $ratio = $this->resolveSubmeterRatio(
                            $subProfile,
                            $industryKey,
                            $month,
                            $trend,
                            $facilityIndex,
                            $mainIdx,
                            $noise
                        );
                        $subActualKwh = round(
                            max(65, $subBaseline * $ratio * $facilityOutageFactor * $mainMaintenanceFactor),
                            2
                        );
                        $subTotal += $subActualKwh;

                        $this->upsertEnergyRecord(
                            (int) $facility->id,
                            (int) $subMeter->id,
                            $year,
                            $month,
                            1,
                            $subActualKwh,
                            $subBaseline,
                            $ratePerKwh,
                            (int) $actor->id
                        );
                    }

                    $lossFactor = 1.045 + ($mainIdx * 0.006) + (($facilityIndex % 3) * 0.003);
                    $mainNoise = 1 + ($this->deterministicNoise($facilityIndex, $mainIdx, 99, $year, $month) * 0.015);
                    $summerMainBoost = in_array($month, [4, 5, 6], true) ? 1.025 : 1.0;
                    $industryMainFactor = max(0.62, 1 + ($this->resolveIndustryMonthAdjustment($industryKey, $month) * 0.85));
                    $baseCommonLoad = round(($mainBaseline * 0.06) * $facilityOutageFactor * $mainMaintenanceFactor, 2);

                    $mainFromSub = ($subTotal * $lossFactor + $baseCommonLoad) * $industryMainFactor;
                    $mainFloorDemand = $mainBaseline
                        * (0.92 + ($trend * 0.35))
                        * $summerMainBoost
                        * $facilityOutageFactor
                        * $mainMaintenanceFactor
                        * $industryMainFactor;
                    $mainActualKwh = round(max($mainFromSub, $mainFloorDemand) * $mainNoise, 2);

                    $this->upsertEnergyRecord(
                        (int) $facility->id,
                        (int) $mainMeter->id,
                        $year,
                        $month,
                        1,
                        $mainActualKwh,
                        $mainBaseline,
                        $ratePerKwh,
                        (int) $actor->id
                    );
                }
            }
        }
    }

    private function buildMainDefinitions(int $facilityIndex, string $industryKey): array
    {
        $mainCount = $facilityIndex % 2 === 0 ? 2 : 3;
        $definitions = [];

        for ($mainIdx = 0; $mainIdx < $mainCount; $mainIdx++) {
            $mainNumber = $mainIdx + 1;
            $mainBaseline = round(6400 + ($facilityIndex * 420) + ($mainIdx * 380), 2);
            $subCount = $mainIdx % 2 === 0 ? 2 : 3;
            $weights = $subCount === 2 ? [0.38, 0.34] : [0.31, 0.27, 0.22];

            $subDefs = [];
            for ($subIdx = 0; $subIdx < $subCount; $subIdx++) {
                $suffix = chr(65 + $subIdx); // A/B/C
                $profile = $this->resolveSubmeterProfile($industryKey, $facilityIndex, $mainIdx, $subIdx);
                $subDefs[] = [
                    'name' => "Main {$mainNumber} - Load Block {$suffix}",
                    'number' => sprintf('S10-%02d-%02d-%02d', $facilityIndex + 1, $mainNumber, $subIdx + 1),
                    'baseline_kwh' => round($mainBaseline * ($weights[$subIdx] ?? 0.25), 2),
                    'profile' => $profile,
                ];
            }

            $definitions[] = [
                'name' => "Main Utility Meter {$mainNumber}",
                'number' => sprintf('M10-%02d-%02d', $facilityIndex + 1, $mainNumber),
                'baseline_kwh' => $mainBaseline,
                'submeters' => $subDefs,
            ];
        }

        return $definitions;
    }

    private function resolveTariffRatePerKwh(int $year, int $month, int $facilityIndex): float
    {
        $baseRate = 11.25 + ($facilityIndex * 0.06);
        $annualInflation = max(0, $year - 2025) * 0.48;
        $summerGenerationCharge = in_array($month, [4, 5, 6], true) ? 0.65 : 0.0;
        $rainyAncillaryCharge = in_array($month, [8, 9, 10], true) ? 0.18 : 0.0;
        $fuelAdjustment = ($month % 4 === 0) ? 0.12 : 0.0;

        return round(
            $baseRate + $annualInflation + $summerGenerationCharge + $rainyAncillaryCharge + $fuelAdjustment,
            2
        );
    }

    private function resolveFacilityOutageFactor(int $facilityIndex, int $year, int $month): float
    {
        $majorOutageMonth = (($facilityIndex * 4) + $year) % 12 + 1;
        $minorOutageMonth = (($facilityIndex * 7) + $year + 3) % 12 + 1;

        if ($month === $majorOutageMonth) {
            return 0.88;
        }
        if ($month === $minorOutageMonth) {
            return 0.95;
        }

        return 1.0;
    }

    private function resolveMainMaintenanceFactor(int $facilityIndex, int $mainIdx, int $year, int $month): float
    {
        $maintenanceMonth = (($facilityIndex + ($mainIdx * 3) + $year) % 12) + 1;
        return $month === $maintenanceMonth ? 0.93 : 1.0;
    }

    private function resolveSubmeterProfile(string $industryKey, int $facilityIndex, int $mainIdx, int $subIdx): string
    {
        $profilePools = [
            'admin' => ['office_it', 'lighting', 'hvac', 'pump'],
            'market' => ['cold_storage', 'lighting', 'hvac', 'pump'],
            'sports' => ['events', 'lighting', 'hvac', 'pump'],
            'health' => ['medical', 'hvac', 'lighting', 'office_it'],
            'library' => ['library', 'lighting', 'hvac', 'office_it'],
            'engineering' => ['pump', 'office_it', 'lighting', 'hvac'],
            'social' => ['office_it', 'lighting', 'events', 'hvac'],
            'transport' => ['lighting', 'pump', 'office_it', 'hvac'],
            'disaster' => ['medical', 'pump', 'office_it', 'lighting'],
            'education' => ['office_it', 'lighting', 'library', 'hvac'],
        ];

        $pool = $profilePools[$industryKey] ?? ['office_it', 'lighting', 'hvac', 'pump'];
        return $pool[($mainIdx + $subIdx) % count($pool)];
    }

    private function resolveSubmeterRatio(
        string $profile,
        string $industryKey,
        int $month,
        float $trend,
        int $facilityIndex,
        int $mainIdx,
        float $noise
    ): float {
        $summerBoost = in_array($month, [4, 5, 6], true) ? 0.09 : 0.0;
        $rainyBoost = in_array($month, [8, 9, 10], true) ? 0.05 : 0.0;
        $decemberDip = $month === 12 ? -0.04 : 0.0;
        $facilityShape = ($facilityIndex * 0.004) + ($mainIdx * 0.003);
        $baseNoise = $noise * 0.018;

        $industryMonthAdjustment = $this->resolveIndustryMonthAdjustment($industryKey, $month);

        $ratio = match ($profile) {
            'hvac' => 0.98 + ($summerBoost * 1.25) + ($trend * 0.75) + $facilityShape + $baseNoise,
            'lighting' => 0.96 + ($rainyBoost * 0.9) + ($trend * 0.25) + $decemberDip + $baseNoise,
            'office_it' => 1.00 + ($trend * 0.45) + ($summerBoost * 0.35) + $baseNoise,
            'pump' => 0.95 + ($rainyBoost * 1.3) + ($trend * 0.3) + $baseNoise,
            'cold_storage' => 1.05 + ($summerBoost * 0.95) + ($trend * 0.35) + $baseNoise,
            'events' => 0.92 + ($month === 12 ? 0.16 : 0.0) + ($summerBoost * 0.4) + ($trend * 0.2) + $baseNoise,
            'medical' => 1.01 + ($summerBoost * 0.5) + ($trend * 0.35) + ($noise * 0.01),
            'library' => 0.94 + ($month === 6 ? 0.1 : 0.0) + ($month === 11 ? 0.06 : 0.0) + ($trend * 0.2) + $baseNoise,
            default => 0.98 + ($trend * 0.3) + $baseNoise,
        };

        return max(0.72, $ratio + $industryMonthAdjustment);
    }

    private function resolveIndustryMonthAdjustment(string $industryKey, int $month): float
    {
        return match ($industryKey) {
            'admin' => in_array($month, [3, 4, 10], true) ? 0.02 : 0.0,
            'market' => in_array($month, [4, 5, 11, 12], true) ? 0.05 : 0.01,
            'sports' => in_array($month, [4, 5, 12], true) ? 0.07 : 0.0,
            'health' => in_array($month, [8, 9, 10], true) ? 0.04 : 0.02,
            'library' => in_array($month, [6, 7, 11], true) ? 0.05 : -0.01,
            'engineering' => in_array($month, [7, 8, 9, 10], true) ? 0.04 : 0.0,
            'social' => in_array($month, [8, 9, 12], true) ? 0.05 : 0.01,
            'transport' => in_array($month, [4, 11, 12], true) ? 0.06 : 0.01,
            'disaster' => in_array($month, [8, 9, 10], true) ? 0.08 : 0.02,
            'education' => in_array($month, [6, 7, 8, 9, 10, 11], true)
                ? 0.10
                : (in_array($month, [4, 5], true) ? -0.32 : -0.05),
            default => 0.0,
        };
    }

    private function deterministicNoise(
        int $facilityIndex,
        int $mainIdx,
        int $subIdx,
        int $year,
        int $month
    ): float {
        $key = $facilityIndex . '|' . $mainIdx . '|' . $subIdx . '|' . $year . '|' . $month;
        $hash = crc32($key);
        $scaled = ($hash % 2001) / 1000; // 0..2.000
        return $scaled - 1.0; // -1.000..1.000
    }

    private function buildMonthRange(Carbon $from, Carbon $to): array
    {
        $months = [];
        $cursor = $from->copy()->startOfMonth();
        $end = $to->copy()->startOfMonth();

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

        return User::query()->create([
            'full_name' => 'Seeder Admin',
            'name' => 'Seeder Admin',
            'email' => 'ten-facility-seeder@example.com',
            'username' => 'tenfacilityseeder',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'department' => 'IT',
            'contact_number' => '09120000000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function upsertFacility(array $attributes): Facility
    {
        $name = (string) ($attributes['name'] ?? 'Seeder Facility');
        $payload = $this->filterColumns('facilities', array_merge($attributes, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        $facility = Facility::withTrashed()->where('name', $name)->first();
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

    private function upsertMeter(int $facilityId, array $attributes): FacilityMeter
    {
        $meterName = (string) ($attributes['meter_name'] ?? 'Seeded Meter');
        $meterType = (string) ($attributes['meter_type'] ?? 'sub');
        $payload = $this->filterColumns('facility_meters', array_merge($attributes, [
            'facility_id' => $facilityId,
            'updated_at' => now(),
            'created_at' => now(),
        ]));

        $meter = FacilityMeter::withTrashed()
            ->where('facility_id', $facilityId)
            ->where('meter_name', $meterName)
            ->where('meter_type', $meterType)
            ->first();

        if (! $meter) {
            $meter = new FacilityMeter();
        }

        if ($meter->trashed()) {
            $meter->restore();
        }

        $meter->fill(collect($payload)->except(['created_at'])->all());
        $meter->save();

        return $meter->refresh();
    }

    private function upsertEnergyProfile(int $facilityId, int $primaryMeterId, int $mainCount, int $subCount, float $baselineKwh): void
    {
        if (! Schema::hasTable('energy_profiles')) {
            return;
        }

        $payload = $this->filterColumns('energy_profiles', [
            'facility_id' => $facilityId,
            'primary_meter_id' => $primaryMeterId,
            'electric_meter_no' => 'M10-PRIMARY',
            'utility_provider' => 'Meralco',
            'contract_account_no' => 'CA-10F-' . str_pad((string) $facilityId, 4, '0', STR_PAD_LEFT),
            'baseline_kwh' => round($baselineKwh, 2),
            'main_energy_source' => 'Electricity',
            'backup_power' => 'Generator',
            'transformer_capacity' => '150 kVA',
            'number_of_meters' => $mainCount + $subCount,
            'baseline_source' => 'historical_data',
            'engineer_approved' => true,
            'baseline_locked' => true,
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        $existing = EnergyProfile::query()->where('facility_id', $facilityId)->first();
        if ($existing) {
            $existing->fill(collect($payload)->except(['facility_id', 'created_at'])->all());
            $existing->save();
            return;
        }

        DB::table('energy_profiles')->insert($payload);
    }

    private function upsertEnergyRecord(
        int $facilityId,
        int $meterId,
        int $year,
        int $month,
        int $day,
        float $actualKwh,
        float $baselineKwh,
        float $ratePerKwh,
        int $recordedBy
    ): void {
        $deviation = EnergyRecord::calculateDeviation($actualKwh, $baselineKwh);
        $alert = EnergyRecord::resolveAlertLevel($deviation, $baselineKwh);

        $values = $this->filterColumns('energy_records', [
            'facility_id' => $facilityId,
            'meter_id' => $meterId,
            'year' => $year,
            'month' => $month,
            'day' => $day,
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
            $query->where('day', $day);
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
