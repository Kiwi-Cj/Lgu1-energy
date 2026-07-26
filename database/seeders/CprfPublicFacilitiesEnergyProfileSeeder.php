<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CprfPublicFacilitiesEnergyProfileSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('facilities')
            || ! Schema::hasTable('facility_meters')
            || ! Schema::hasTable('energy_profiles')) {
            return;
        }

        $actorId = User::query()
            ->whereIn('role', ['super_admin', 'super admin', 'admin'])
            ->orderBy('id')
            ->value('id');

        Facility::query()
            ->where('source', 'cprf')
            ->orderBy('id')
            ->get()
            ->each(function (Facility $facility) use ($actorId): void {
                DB::transaction(function () use ($facility, $actorId): void {
                    $baselineKwh = $this->baselineFor($facility);
                    $reference = $this->referenceFor($facility);
                    $meterNumber = 'CPRF-MAIN-' . $reference;

                    $meter = DB::table('facility_meters')
                        ->where('facility_id', $facility->id)
                        ->where('meter_type', 'main')
                        ->whereNull('deleted_at')
                        ->orderBy('id')
                        ->first();

                    $meterDefaults = $this->filterColumns('facility_meters', [
                        'facility_id' => $facility->id,
                        'meter_name' => $facility->name . ' Main Meter',
                        'meter_number' => $meterNumber,
                        'meter_type' => 'main',
                        'location' => 'Main Electrical Panel',
                        'status' => 'active',
                        'multiplier' => 1,
                        'baseline_kwh' => $baselineKwh,
                        'notes' => 'Seeded energy profile main meter for CPRF public facility',
                        'approved_by_user_id' => $actorId,
                        'approved_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                        'deleted_at' => null,
                    ]);

                    if ($meter) {
                        $meterPayload = $this->fillMissing((array) $meter, $meterDefaults);
                        $meterPayload['approved_at'] = $meter->approved_at ?: now();
                        if ($actorId && empty($meter->approved_by_user_id)) {
                            $meterPayload['approved_by_user_id'] = $actorId;
                        }
                        $meterPayload['updated_at'] = now();
                        unset($meterPayload['id'], $meterPayload['created_at']);

                        DB::table('facility_meters')->where('id', $meter->id)->update($meterPayload);
                        $meterId = (int) $meter->id;
                        $meterNumber = trim((string) ($meterPayload['meter_number'] ?? $meterNumber)) ?: $meterNumber;
                    } else {
                        $meterId = (int) DB::table('facility_meters')->insertGetId($meterDefaults);
                    }

                    $profileDefaults = $this->filterColumns('energy_profiles', [
                        'facility_id' => $facility->id,
                        'primary_meter_id' => $meterId,
                        'electric_meter_no' => $meterNumber,
                        'utility_provider' => 'Meralco',
                        'contract_account_no' => 'CPRF-CA-' . $reference,
                        'baseline_kwh' => $baselineKwh,
                        'main_energy_source' => 'Electricity',
                        'backup_power' => $this->backupPowerFor($facility),
                        'transformer_capacity' => $this->transformerCapacityFor($baselineKwh),
                        'number_of_meters' => 1,
                        'engineer_approved' => true,
                        'baseline_locked' => true,
                        'baseline_source' => 'cprf_profile_seeder',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $profile = DB::table('energy_profiles')
                        ->where('facility_id', $facility->id)
                        ->orderBy('id')
                        ->first();

                    if ($profile) {
                        $profilePayload = $this->fillMissing((array) $profile, $profileDefaults);
                        $profilePayload['primary_meter_id'] = $profile->primary_meter_id ?: $meterId;
                        $profilePayload['updated_at'] = now();
                        unset($profilePayload['id'], $profilePayload['created_at']);

                        DB::table('energy_profiles')->where('id', $profile->id)->update($profilePayload);
                    } else {
                        DB::table('energy_profiles')->insert($profileDefaults);
                    }

                    $facilityUpdates = $this->filterColumns('facilities', [
                        'baseline_kwh' => $facility->baseline_kwh ?: $baselineKwh,
                        'baseline_status' => 'active',
                        'baseline_start_date' => $facility->baseline_start_date ?: now()->startOfYear()->toDateString(),
                        'engineer_approved' => true,
                        'updated_at' => now(),
                    ]);

                    DB::table('facilities')->where('id', $facility->id)->update($facilityUpdates);
                });
            });
    }

    private function baselineFor(Facility $facility): float
    {
        if (is_numeric($facility->baseline_kwh) && (float) $facility->baseline_kwh > 0) {
            return (float) $facility->baseline_kwh;
        }

        $name = Str::lower((string) $facility->name);

        return match (true) {
            Str::contains($name, ['highschool', 'high school']) => 4500,
            Str::contains($name, ['multipurpose bldg', 'multipurpose building']) => 2200,
            Str::contains($name, ['covered court', 'court']) => 1800,
            Str::contains($name, 'outpost') => 600,
            default => 1200,
        };
    }

    private function backupPowerFor(Facility $facility): string
    {
        $name = Str::lower((string) $facility->name);

        return Str::contains($name, ['highschool', 'high school', 'multipurpose'])
            ? 'Generator'
            : 'None';
    }

    private function transformerCapacityFor(float $baselineKwh): string
    {
        return match (true) {
            $baselineKwh >= 4000 => '150 kVA',
            $baselineKwh >= 2000 => '75 kVA',
            default => '50 kVA',
        };
    }

    private function referenceFor(Facility $facility): string
    {
        $reference = preg_replace('/[^A-Za-z0-9]/', '', (string) $facility->external_ref);

        return $reference !== ''
            ? str_pad($reference, 3, '0', STR_PAD_LEFT)
            : str_pad((string) $facility->id, 3, '0', STR_PAD_LEFT);
    }

    private function fillMissing(array $existing, array $defaults): array
    {
        foreach ($defaults as $column => $value) {
            if (! array_key_exists($column, $existing)
                || $existing[$column] === null
                || $existing[$column] === ''
                || (in_array($column, ['baseline_kwh', 'number_of_meters'], true) && (float) $existing[$column] <= 0)) {
                $existing[$column] = $value;
            }
        }

        return $existing;
    }

    private function filterColumns(string $table, array $attributes): array
    {
        $columns = Schema::getColumnListing($table);

        return array_filter(
            $attributes,
            fn ($key) => in_array($key, $columns, true),
            ARRAY_FILTER_USE_KEY
        );
    }
}
