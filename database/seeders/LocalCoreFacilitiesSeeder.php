<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class LocalCoreFacilitiesSeeder extends Seeder
{
    public function run(): void
    {
        $actor = $this->resolveActorUser();
        $facilities = [
            [
                'name' => 'Police Station',
                'type' => 'Government Office',
                'location' => 'Poblacion',
                'address' => 'Police Station Road, Poblacion',
                'barangay' => 'Poblacion',
                'department' => 'Philippine National Police',
                'floor_area' => 1200,
                'floor_area_sqm' => 1200,
                'floors' => 2,
                'year_built' => 2016,
                'operating_hours' => '24/7',
                'baseline_kwh' => 1800,
                'baseline_status' => 'active',
                'baseline_start_date' => '2025-01-01',
                'status' => 'active',
                'engineer_approved' => true,
                'meter' => [
                    'meter_name' => 'Police Station Main Meter',
                    'meter_number' => 'PS-0001',
                    'location' => 'Main Electrical Room',
                ],
                'profile' => [
                    'utility_provider' => 'Meralco',
                    'contract_account_no' => 'CA-PS-0001',
                    'main_energy_source' => 'Electricity',
                    'backup_power' => 'Generator',
                    'transformer_capacity' => '75 kVA',
                    'number_of_meters' => 1,
                ],
            ],
            [
                'name' => 'Munisipyo',
                'type' => 'Government Office',
                'location' => 'Municipal Hall',
                'address' => 'Municipal Hall Drive, Poblacion',
                'barangay' => 'Poblacion',
                'department' => "Mayor's Office",
                'floor_area' => 2500,
                'floor_area_sqm' => 2500,
                'floors' => 3,
                'year_built' => 2018,
                'operating_hours' => '8:00 AM - 5:00 PM',
                'baseline_kwh' => 4200,
                'baseline_status' => 'active',
                'baseline_start_date' => '2025-01-01',
                'status' => 'active',
                'engineer_approved' => true,
                'meter' => [
                    'meter_name' => 'Munisipyo Main Meter',
                    'meter_number' => 'MUN-0001',
                    'location' => 'Ground Floor Electrical Room',
                ],
                'profile' => [
                    'utility_provider' => 'Meralco',
                    'contract_account_no' => 'CA-MUN-0001',
                    'main_energy_source' => 'Electricity',
                    'backup_power' => 'Generator',
                    'transformer_capacity' => '150 kVA',
                    'number_of_meters' => 1,
                ],
            ],
        ];

        foreach ($facilities as $facility) {
            $payload = $this->filterColumns('facilities', array_merge(collect($facility)->except(['meter', 'profile'])->all(), [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            $existing = Facility::withTrashed()->where('name', $facility['name'])->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }

                $existing->forceFill(collect($payload)->except(['created_at'])->all());
                $existing->save();
            }

            if (! $existing) {
                DB::table('facilities')->insert($payload);
                $existing = Facility::query()->where('name', $facility['name'])->first();
            }

            if (! $existing) {
                continue;
            }

            $meterPayload = $this->filterColumns('facility_meters', [
                'facility_id' => $existing->id,
                'meter_name' => $facility['meter']['meter_name'],
                'meter_number' => $facility['meter']['meter_number'],
                'meter_type' => 'main',
                'location' => $facility['meter']['location'],
                'status' => 'active',
                'multiplier' => 1,
                'baseline_kwh' => $facility['baseline_kwh'],
                'notes' => 'Seeded local core facility main meter',
                'approved_by_user_id' => $actor->id,
                'approved_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
                'deleted_at' => null,
            ]);

            $meterRow = DB::table('facility_meters')
                ->where('facility_id', $existing->id)
                ->where('meter_name', $facility['meter']['meter_name'])
                ->where('meter_type', 'main')
                ->first();

            if ($meterRow) {
                DB::table('facility_meters')
                    ->where('id', $meterRow->id)
                    ->update(collect($meterPayload)->except(['created_at'])->all());
                $meterId = (int) $meterRow->id;
            } else {
                $meterId = (int) DB::table('facility_meters')->insertGetId($meterPayload);
            }

            $profilePayload = $this->filterColumns('energy_profiles', [
                'facility_id' => $existing->id,
                'primary_meter_id' => $meterId,
                'electric_meter_no' => $facility['meter']['meter_number'],
                'utility_provider' => $facility['profile']['utility_provider'],
                'contract_account_no' => $facility['profile']['contract_account_no'],
                'baseline_kwh' => $facility['baseline_kwh'],
                'main_energy_source' => $facility['profile']['main_energy_source'],
                'backup_power' => $facility['profile']['backup_power'],
                'transformer_capacity' => $facility['profile']['transformer_capacity'],
                'number_of_meters' => $facility['profile']['number_of_meters'],
                'engineer_approved' => true,
                'baseline_locked' => true,
                'baseline_source' => 'manual_seed',
                'updated_at' => now(),
                'created_at' => now(),
            ]);

            $profileRow = DB::table('energy_profiles')
                ->where('facility_id', $existing->id)
                ->first();

            if ($profileRow) {
                DB::table('energy_profiles')
                    ->where('id', $profileRow->id)
                    ->update(collect($profilePayload)->except(['created_at'])->all());
            } else {
                DB::table('energy_profiles')->insert($profilePayload);
            }
        }
    }

    private function resolveActorUser(): User
    {
        $user = User::query()
            ->whereIn('role', ['super_admin', 'super admin', 'admin'])
            ->orderBy('id')
            ->first();

        if ($user) {
            return $user;
        }

        return User::query()->create([
            'full_name' => 'Local Seeder Admin',
            'name' => 'Local Seeder Admin',
            'email' => 'local-seeder-admin@example.com',
            'username' => 'localseederadmin',
            'password' => Hash::make('password'),
            'role' => 'super admin',
            'status' => 'active',
            'department' => 'Admin',
            'contact_number' => '09120000001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function filterColumns(string $table, array $attributes): array
    {
        $columns = Schema::hasTable($table) ? Schema::getColumnListing($table) : [];

        return array_filter(
            $attributes,
            fn ($key) => in_array($key, $columns, true),
            ARRAY_FILTER_USE_KEY
        );
    }
}
