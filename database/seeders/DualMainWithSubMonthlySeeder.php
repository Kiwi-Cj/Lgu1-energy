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

class DualMainWithSubMonthlySeeder extends Seeder
{
    private array $columnCache = [];

    public function run(): void
    {
        $actor = $this->resolveActorUser();

        $facility = $this->upsertFacility([
            'name' => '[Seeder 2M] Dual Main Meter Facility',
            'type' => 'Government Office',
            'department' => 'General Services',
            'address' => 'LGU Compound',
            'barangay' => 'Poblacion',
            'location' => 'Poblacion',
            'floor_area' => 3200,
            'status' => 'active',
            'baseline_kwh' => 18200,
            'baseline_status' => 'active',
            'baseline_start_date' => '2025-01-01',
            'engineer_approved' => true,
        ]);

        $this->attachUserToFacility((int) $actor->id, (int) $facility->id);

        $mainDefinitions = [
            [
                'name' => 'Main Utility Meter A',
                'number' => 'M2M-0001',
                'baseline_kwh' => 9000,
                'submeters' => [
                    ['name' => 'A - Engineering Office', 'number' => 'S2M-A01', 'baseline_kwh' => 2650],
                    ['name' => 'A - Aircon Plant Room', 'number' => 'S2M-A02', 'baseline_kwh' => 2450],
                ],
            ],
            [
                'name' => 'Main Utility Meter B',
                'number' => 'M2M-0002',
                'baseline_kwh' => 9200,
                'submeters' => [
                    ['name' => 'B - Library Wing', 'number' => 'S2M-B01', 'baseline_kwh' => 2520],
                    ['name' => 'B - Health Unit Loads', 'number' => 'S2M-B02', 'baseline_kwh' => 2380],
                ],
            ],
        ];

        $mainMeters = [];
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
                'notes' => 'Seeded main meter for dual-main dataset',
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
                    'location' => 'Sub Panel',
                    'status' => 'active',
                    'multiplier' => 1.0,
                    'baseline_kwh' => (float) $subDef['baseline_kwh'],
                    'notes' => 'Seeded sub meter for dual-main dataset',
                    'approved_by_user_id' => (int) $actor->id,
                    'approved_at' => now(),
                ]);
            }

            $mainMeters[] = [
                'main' => $mainMeter,
                'main_baseline_kwh' => (float) $mainDef['baseline_kwh'],
                'submeters' => $submeters,
                'sub_defs' => $mainDef['submeters'],
            ];
        }

        $primaryMain = $mainMeters[0]['main'] ?? null;
        if ($primaryMain) {
            $this->upsertEnergyProfile((int) $facility->id, (int) $primaryMain->id);
        }

        $months = $this->buildMonthRange(
            Carbon::create(2025, 1, 1)->startOfMonth(),
            now()->copy()->startOfMonth()
        );

        foreach ($months as $monthIndex => $monthStart) {
            $year = (int) $monthStart->year;
            $month = (int) $monthStart->month;
            $seasonal = sin(($month / 12) * 2 * M_PI) * 0.045;
            $trend = $monthIndex * 0.0025;
            $ratePerKwh = round(11.85 + (($year - 2025) * 0.22) + (($month - 1) * 0.03), 2);

            foreach ($mainMeters as $mainIdx => $mainRow) {
                $mainMeter = $mainRow['main'];
                $mainBaseline = (float) $mainRow['main_baseline_kwh'];

                $subTotal = 0.0;
                foreach ($mainRow['submeters'] as $subIdx => $subMeter) {
                    $subBaseline = (float) ($mainRow['sub_defs'][$subIdx]['baseline_kwh'] ?? 0);
                    $shape = (($mainIdx + 1) * 0.006) + (($subIdx + 1) * 0.004);
                    $ratio = 1 + $seasonal + $trend + $shape;
                    $subActual = round(max(120, $subBaseline * $ratio), 2);
                    $subTotal += $subActual;

                    $this->upsertEnergyRecord(
                        (int) $facility->id,
                        (int) $subMeter->id,
                        $year,
                        $month,
                        1,
                        $subActual,
                        $subBaseline,
                        $ratePerKwh,
                        (int) $actor->id
                    );
                }

                $mainActual = round(max($subTotal + 180, ($mainBaseline * (1 + $seasonal + ($trend / 2)))), 2);

                $this->upsertEnergyRecord(
                    (int) $facility->id,
                    (int) $mainMeter->id,
                    $year,
                    $month,
                    1,
                    $mainActual,
                    $mainBaseline,
                    $ratePerKwh,
                    (int) $actor->id
                );
            }
        }
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
            'email' => 'dual-main-seeder@example.com',
            'username' => 'dualmainseeder',
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

    private function upsertEnergyProfile(int $facilityId, int $primaryMeterId): void
    {
        if (! Schema::hasTable('energy_profiles')) {
            return;
        }

        $payload = $this->filterColumns('energy_profiles', [
            'facility_id' => $facilityId,
            'primary_meter_id' => $primaryMeterId,
            'electric_meter_no' => 'M2M-0001',
            'utility_provider' => 'Meralco',
            'contract_account_no' => 'CA-2M-' . str_pad((string) $facilityId, 4, '0', STR_PAD_LEFT),
            'baseline_kwh' => 18200,
            'main_energy_source' => 'Electricity',
            'backup_power' => 'Generator',
            'transformer_capacity' => '150 kVA',
            'number_of_meters' => 6,
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
