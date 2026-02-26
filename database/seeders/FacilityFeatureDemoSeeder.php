<?php

namespace Database\Seeders;

use App\Models\EnergyProfile;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityAuditLog;
use App\Models\FacilityMeter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class FacilityFeatureDemoSeeder extends Seeder
{
    private array $columnCache = [];

    public function run(): void
    {
        $now = now();
        $actor = $this->resolveActorUser();

        $activeFacility = $this->upsertFacility(
            '[Seeder Demo] City Hall Annex',
            [
                'type' => 'Government Office',
                'department' => 'General Services Office',
                'address' => 'Rizal Street, Poblacion',
                'barangay' => 'Poblacion',
                'location' => 'Poblacion',
                'floor_area' => 2500.00,
                'floors' => 3,
                'status' => 'active',
                'baseline_kwh' => 12000,
                'baseline_status' => 'active',
                'baseline_start_date' => $now->copy()->startOfYear()->toDateString(),
                'operating_hours' => '8AM-5PM',
                'year_built' => 2018,
                'engineer_approved' => true,
            ]
        );

        $this->attachUserToFacility($actor->id, $activeFacility->id);

        $mainMeter = $this->upsertMeter($activeFacility->id, 'Main Utility Meter', [
            'meter_number' => 'MER-' . $activeFacility->id . '-0001',
            'meter_type' => 'main',
            'status' => 'active',
            'location' => 'Ground Floor Electrical Room',
            'multiplier' => 1,
            'baseline_kwh' => 7900,
            'notes' => 'Primary utility billing meter',
            'deleted_by' => null,
            'archive_reason' => null,
        ]);

        $subMeterA = $this->upsertMeter($activeFacility->id, '2nd Floor Offices', [
            'meter_number' => 'SUB-' . $activeFacility->id . '-2001',
            'meter_type' => 'sub',
            'parent_meter_id' => $mainMeter->id,
            'status' => 'active',
            'location' => '2nd Floor Panel Board',
            'multiplier' => 1,
            'baseline_kwh' => 4200,
            'notes' => 'Office load sub-meter',
            'deleted_by' => null,
            'archive_reason' => null,
        ]);

        $subMeterB = $this->upsertMeter($activeFacility->id, 'Aircon Plant Room', [
            'meter_number' => 'SUB-' . $activeFacility->id . '-3001',
            'meter_type' => 'sub',
            'parent_meter_id' => $mainMeter->id,
            'status' => 'active',
            'location' => 'Mechanical Room',
            'multiplier' => 1,
            'baseline_kwh' => 3400,
            'notes' => 'Dedicated HVAC load monitoring',
            'deleted_by' => null,
            'archive_reason' => null,
        ]);

        $archivedMeter = $this->upsertMeter($activeFacility->id, 'Old Lobby Meter', [
            'meter_number' => 'SUB-' . $activeFacility->id . '-OLD1',
            'meter_type' => 'sub',
            'parent_meter_id' => $mainMeter->id,
            'status' => 'inactive',
            'location' => 'Old Lobby Panel',
            'multiplier' => 1,
            'baseline_kwh' => 900,
            'notes' => 'Replaced after renovation',
        ]);
        $this->archiveMeter($archivedMeter, $actor->id, 'Replaced by new sub-meter after renovation.', $now->copy()->subDays(10));

        $profileAttrs = $this->filterColumns('energy_profiles', [
            'facility_id' => $activeFacility->id,
            'primary_meter_id' => $mainMeter->id,
            'electric_meter_no' => $mainMeter->meter_number,
            'utility_provider' => 'Meralco',
            'contract_account_no' => 'CA-' . str_pad((string) $activeFacility->id, 6, '0', STR_PAD_LEFT),
            'baseline_kwh' => 12000,
            'main_energy_source' => 'Electricity',
            'backup_power' => 'Generator',
            'transformer_capacity' => '100 kVA',
            'number_of_meters' => 4, // includes archived sample meter for demo
            'baseline_source' => 'historical_data',
            'engineer_approved' => true,
            'baseline_locked' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $profile = EnergyProfile::query()->where('facility_id', $activeFacility->id)->first();
        if ($profile) {
            $profile->fill(collect($profileAttrs)->except(['facility_id', 'created_at'])->all());
            $profile->save();
        } else {
            DB::table('energy_profiles')->insert($profileAttrs);
            $profile = EnergyProfile::query()->where('facility_id', $activeFacility->id)->latest('id')->first();
        }

        $months = range(1, max(2, min(3, (int) $now->month)));
        $year = (int) $now->year;
        $baseline = 12000.0;

        Model::withoutEvents(function () use ($activeFacility, $mainMeter, $subMeterA, $subMeterB, $actor, $months, $year, $baseline) {
            foreach ($months as $index => $month) {
                $subA = 4300 + ($index * 120);
                $subB = 3500 + ($index * 90);
                $subTotal = $subA + $subB;
                $mainActual = $subTotal + (250 + ($index * 20));
                $aggregateActual = $mainActual + (80 + ($index * 10));

                $this->upsertEnergyRecord($activeFacility->id, null, $year, $month, [
                    'actual_kwh' => $aggregateActual,
                    'energy_cost' => round($aggregateActual * 12.85, 2),
                    'rate_per_kwh' => 12.85,
                    'recorded_by' => $actor->id,
                    'baseline_kwh' => $baseline,
                    'deviation' => round((($aggregateActual - $baseline) / $baseline) * 100, 2),
                    'alert' => abs((($aggregateActual - $baseline) / $baseline) * 100) > 10 ? 'Warning' : 'Normal',
                ]);

                $this->upsertEnergyRecord($activeFacility->id, $mainMeter->id, $year, $month, [
                    'actual_kwh' => $mainActual,
                    'energy_cost' => round($mainActual * 12.85, 2),
                    'rate_per_kwh' => 12.85,
                    'recorded_by' => $actor->id,
                    'baseline_kwh' => $baseline,
                    'deviation' => round((($mainActual - $baseline) / $baseline) * 100, 2),
                    'alert' => abs((($mainActual - $baseline) / $baseline) * 100) > 10 ? 'Warning' : 'Normal',
                ]);

                $this->upsertEnergyRecord($activeFacility->id, $subMeterA->id, $year, $month, [
                    'actual_kwh' => $subA,
                    'energy_cost' => round($subA * 12.85, 2),
                    'rate_per_kwh' => 12.85,
                    'recorded_by' => $actor->id,
                    'baseline_kwh' => 4200,
                    'deviation' => round((($subA - 4200) / 4200) * 100, 2),
                    'alert' => abs((($subA - 4200) / 4200) * 100) > 15 ? 'Warning' : 'Normal',
                ]);

                $this->upsertEnergyRecord($activeFacility->id, $subMeterB->id, $year, $month, [
                    'actual_kwh' => $subB,
                    'energy_cost' => round($subB * 12.85, 2),
                    'rate_per_kwh' => 12.85,
                    'recorded_by' => $actor->id,
                    'baseline_kwh' => 3400,
                    'deviation' => round((($subB - 3400) / 3400) * 100, 2),
                    'alert' => abs((($subB - 3400) / 3400) * 100) > 15 ? 'Warning' : 'Normal',
                ]);
            }
        });

        $archivedRecord = $this->upsertEnergyRecord($activeFacility->id, $subMeterA->id, $year - 1, 12, [
            'actual_kwh' => 3980,
            'energy_cost' => round(3980 * 12.15, 2),
            'rate_per_kwh' => 12.15,
            'recorded_by' => $actor->id,
            'baseline_kwh' => 4200,
            'deviation' => round(((3980 - 4200) / 4200) * 100, 2),
            'alert' => 'Normal',
        ]);
        $this->archiveEnergyRecord($archivedRecord, $now->copy()->subDays(7));

        $this->upsertFacilityAuditLog($activeFacility, 'archived', 'Sample archive event for audit trail filtering.', $actor->id, $now->copy()->subDays(15));
        $this->upsertFacilityAuditLog($activeFacility, 'restored', 'Sample restore event for audit trail filtering.', $actor->id, $now->copy()->subDays(14));
        $this->upsertFacilityAuditLog($activeFacility, 'permanently_deleted', 'Sample permanent delete log entry for audit trail filter demo.', $actor->id, $now->copy()->subDays(13));

        $archivedFacility = $this->upsertFacility(
            '[Seeder Demo] Archived Pump Station',
            [
                'type' => 'Utility Facility',
                'department' => 'Engineering',
                'address' => 'Barangay San Jose',
                'barangay' => 'San Jose',
                'location' => 'San Jose',
                'floor_area' => 320.00,
                'status' => 'inactive',
                'baseline_kwh' => 1800,
                'baseline_status' => 'inactive',
                'engineer_approved' => true,
            ]
        );
        $this->archiveFacility($archivedFacility, $actor->id, 'Decommissioned and merged into new pumping station.', $now->copy()->subDays(20));
        $this->upsertFacilityAuditLog($archivedFacility, 'archived', 'Decommissioned and merged into new pumping station.', $actor->id, $now->copy()->subDays(20));
    }

    private function resolveActorUser(): User
    {
        $actor = User::query()
            ->whereIn('role', ['super_admin', 'super admin', 'admin', 'energy_officer'])
            ->orderBy('id')
            ->first();

        if ($actor) {
            return $actor;
        }

        $now = now();
        $user = User::query()->where('email', 'facility-seeder@example.com')->first();
        if ($user) {
            return $user;
        }

        return User::query()->create([
            'full_name' => 'Facility Seeder Admin',
            'name' => 'Facility Seeder Admin',
            'email' => 'facility-seeder@example.com',
            'username' => 'facilityseeder',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'department' => 'IT',
            'contact_number' => '09123456789',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function upsertFacility(string $name, array $attributes): Facility
    {
        $now = now();
        $payload = $this->filterColumns('facilities', array_merge([
            'name' => $name,
            'created_at' => $now,
            'updated_at' => $now,
        ], $attributes));

        $facility = Facility::withTrashed()->where('name', $name)->first();
        if ($facility) {
            $facility->forceFill(collect($payload)->except(['created_at'])->all());
            $facility->save();
            return $facility->refresh();
        }

        $id = DB::table('facilities')->insertGetId($payload);
        return Facility::withTrashed()->findOrFail($id);
    }

    private function attachUserToFacility(int $userId, int $facilityId): void
    {
        if (! Schema::hasTable('facility_user')) {
            return;
        }

        $now = now();
        DB::table('facility_user')->updateOrInsert(
            ['user_id' => $userId, 'facility_id' => $facilityId],
            ['updated_at' => $now, 'created_at' => $now]
        );
    }

    private function upsertMeter(int $facilityId, string $meterName, array $attributes): FacilityMeter
    {
        $meter = FacilityMeter::withTrashed()
            ->where('facility_id', $facilityId)
            ->where('meter_name', $meterName)
            ->first();

        $payload = array_merge(['facility_id' => $facilityId, 'meter_name' => $meterName], $attributes);

        if (! $meter) {
            $meter = new FacilityMeter();
        }

        $meter->fill($payload);
        if ($meter->trashed()) {
            $meter->restore();
        }
        $meter->save();

        return $meter->refresh();
    }

    private function archiveMeter(FacilityMeter $meter, ?int $deletedBy, ?string $reason, Carbon $deletedAt): void
    {
        $meter->deleted_by = $deletedBy;
        $meter->archive_reason = $reason;
        $meter->status = 'inactive';
        $meter->save();

        if (! $meter->trashed()) {
            $meter->delete();
        }

        $meter->forceFill(['deleted_at' => $deletedAt])->saveQuietly();
    }

    private function upsertEnergyRecord(int $facilityId, ?int $meterId, int $year, int $month, array $attributes): EnergyRecord
    {
        $recordColumns = $this->getColumns('energy_records');
        $hasMeterId = in_array('meter_id', $recordColumns, true);
        $hasDay = in_array('day', $recordColumns, true);

        $query = EnergyRecord::withTrashed()
            ->where('facility_id', $facilityId)
            ->where('year', $year)
            ->where('month', $month);

        if ($hasMeterId) {
            $query = $meterId ? $query->where('meter_id', $meterId) : $query->whereNull('meter_id');
        }

        $record = $query->first() ?: new EnergyRecord();

        $payload = array_merge([
            'facility_id' => $facilityId,
            'meter_id' => $meterId,
            'year' => $year,
            'month' => (string) $month,
            'day' => $hasDay ? 1 : null,
        ], $attributes);

        $record->fill($this->filterColumns('energy_records', $payload));

        if ($record->trashed()) {
            $record->restore();
        }

        $record->save();

        return $record->refresh();
    }

    private function archiveEnergyRecord(EnergyRecord $record, Carbon $deletedAt): void
    {
        if (! in_array('deleted_at', $this->getColumns('energy_records'), true)) {
            return;
        }

        if (! $record->trashed()) {
            $record->delete();
        }

        $record->forceFill(['deleted_at' => $deletedAt])->saveQuietly();
    }

    private function archiveFacility(Facility $facility, ?int $deletedBy, string $reason, Carbon $deletedAt): void
    {
        $facility->forceFill([
            'deleted_by' => $deletedBy,
            'archive_reason' => $reason,
        ])->save();

        if (! $facility->trashed()) {
            $facility->delete();
        }

        $updates = [];
        if (Schema::hasColumn('facilities', 'deleted_at')) {
            $updates['deleted_at'] = $deletedAt;
        }
        if (Schema::hasColumn('facilities', 'deleted_by')) {
            $updates['deleted_by'] = $deletedBy;
        }
        if (Schema::hasColumn('facilities', 'archive_reason')) {
            $updates['archive_reason'] = $reason;
        }
        if (Schema::hasColumn('facilities', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        if ($updates !== []) {
            DB::table('facilities')->where('id', $facility->id)->update($updates);
        }
    }

    private function upsertFacilityAuditLog(Facility $facility, string $action, ?string $reason, ?int $performedBy, Carbon $when): void
    {
        if (! Schema::hasTable('facility_audit_logs')) {
            return;
        }

        $log = FacilityAuditLog::query()->firstOrNew([
            'facility_id' => $facility->id,
            'action' => $action,
            'reason' => $reason,
        ]);

        $log->facility_name = $facility->name;
        $log->performed_by = $performedBy;
        $log->created_at = $when;
        $log->updated_at = $when;
        $log->save();
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
