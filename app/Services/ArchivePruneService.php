<?php

namespace App\Services;

use App\Models\EnergyIncident;
use App\Models\EnergyIncidentHistory;
use App\Models\EnergyProfile;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityAuditLog;
use App\Models\FacilityMeter;
use App\Models\Maintenance;
use App\Models\MaintenanceHistory;
use App\Models\Submeter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ArchivePruneService
{
    public function pruneExpired(int $days = 30, bool $dryRun = false): array
    {
        $days = max(1, $days);
        $cutoff = now()->subDays($days);

        $facilityIds = Facility::onlyTrashed()
            ->where('deleted_at', '<=', $cutoff)
            ->orderBy('id')
            ->pluck('id');

        $meterIds = FacilityMeter::onlyTrashed()
            ->where('deleted_at', '<=', $cutoff)
            ->whereNotIn('facility_id', $facilityIds)
            ->orderBy('id')
            ->pluck('id');

        $recordIds = EnergyRecord::onlyTrashed()
            ->where('deleted_at', '<=', $cutoff)
            ->whereNotIn('facility_id', $facilityIds)
            ->orderBy('id')
            ->pluck('id');

        $counts = [
            'cutoff' => $cutoff,
            'facilities' => $facilityIds->count(),
            'meters' => $meterIds->count(),
            'monthly_records' => $recordIds->count(),
        ];

        if ($dryRun) {
            return $counts;
        }

        foreach ($facilityIds as $facilityId) {
            $facility = Facility::onlyTrashed()->find($facilityId);
            if ($facility) {
                $this->permanentlyDeleteFacility($facility, 'Automatic permanent delete after ' . $days . ' days in archive.');
            }
        }

        FacilityMeter::onlyTrashed()
            ->whereIn('id', $meterIds)
            ->orderBy('id')
            ->chunkById(100, function ($meters) {
                foreach ($meters as $meter) {
                    $this->permanentlyDeleteMeter($meter);
                }
            });

        EnergyRecord::onlyTrashed()
            ->whereIn('id', $recordIds)
            ->orderBy('id')
            ->chunkById(100, function ($records) {
                foreach ($records as $record) {
                    $record->forceDelete();
                }
            });

        return $counts;
    }

    public function permanentlyDeleteFacility(Facility $facility, string $logMessage = 'Permanent delete from archive.'): void
    {
        DB::transaction(function () use ($facility, $logMessage) {
            $facilityId = (int) $facility->id;
            $facilityName = (string) $facility->name;
            $archiveReason = trim((string) ($facility->archive_reason ?? ''));

            $this->logFacilityAudit(
                $facility,
                'permanently_deleted',
                $archiveReason !== ''
                    ? $logMessage . ' Original archive reason: ' . $archiveReason
                    : $logMessage
            );

            $energyRecordIds = EnergyRecord::withTrashed()
                ->where('facility_id', $facilityId)
                ->pluck('id');

            if ($energyRecordIds->isNotEmpty()) {
                EnergyIncidentHistory::whereIn('energy_record_id', $energyRecordIds)->delete();
                EnergyIncident::whereIn('energy_record_id', $energyRecordIds)->delete();
                Maintenance::whereIn('energy_record_id', $energyRecordIds)->update(['energy_record_id' => null]);
            }

            $mainMeterReadingIds = DB::table('main_meter_readings')
                ->where('facility_id', $facilityId)
                ->pluck('id');

            if ($mainMeterReadingIds->isNotEmpty()) {
                DB::table('main_meter_alerts')
                    ->whereIn('main_meter_reading_id', $mainMeterReadingIds)
                    ->delete();
            }

            DB::table('main_meter_alerts')->where('facility_id', $facilityId)->delete();
            DB::table('main_meter_baselines')->where('facility_id', $facilityId)->delete();
            DB::table('main_meter_readings')->where('facility_id', $facilityId)->delete();

            $submeterIds = DB::table('submeters')
                ->where('facility_id', $facilityId)
                ->pluck('id');

            if ($submeterIds->isNotEmpty()) {
                $submeterReadingIds = DB::table('submeter_readings')
                    ->whereIn('submeter_id', $submeterIds)
                    ->pluck('id');

                if ($submeterReadingIds->isNotEmpty()) {
                    DB::table('submeter_alerts')
                        ->whereIn('submeter_reading_id', $submeterReadingIds)
                        ->delete();
                }

                DB::table('submeter_alerts')->whereIn('submeter_id', $submeterIds)->delete();
                DB::table('submeter_baselines')->whereIn('submeter_id', $submeterIds)->delete();

                if (Schema::hasTable('submeter_equipment_files')) {
                    DB::table('submeter_equipment_files')->whereIn('submeter_id', $submeterIds)->delete();
                }

                if (Schema::hasTable('submeter_equipments')) {
                    DB::table('submeter_equipments')->whereIn('submeter_id', $submeterIds)->delete();
                }

                DB::table('submeter_readings')->whereIn('submeter_id', $submeterIds)->delete();
            }

            $facilityMeterIds = FacilityMeter::withTrashed()
                ->where('facility_id', $facilityId)
                ->pluck('id');

            if ($facilityMeterIds->isNotEmpty()) {
                if (Schema::hasTable('submeter_equipment_files')) {
                    DB::table('submeter_equipment_files')->whereIn('facility_meter_id', $facilityMeterIds)->delete();
                }

                if (Schema::hasTable('submeter_equipments')) {
                    DB::table('submeter_equipments')->whereIn('facility_meter_id', $facilityMeterIds)->delete();
                }
            }

            EnergyRecord::withTrashed()
                ->where('facility_id', $facilityId)
                ->get()
                ->each(function (EnergyRecord $record) {
                    $record->forceDelete();
                });

            EnergyIncident::where('facility_id', $facilityId)->delete();
            Maintenance::where('facility_id', $facilityId)->delete();
            MaintenanceHistory::where('facility_id', $facilityId)->delete();
            EnergyProfile::where('facility_id', $facilityId)->delete();

            if (Schema::hasTable('baseline_reset_logs')) {
                DB::table('baseline_reset_logs')->where('facility_id', $facilityId)->delete();
            }

            Submeter::whereIn('id', $submeterIds)->delete();
            FacilityMeter::withTrashed()->where('facility_id', $facilityId)->forceDelete();

            $facility->users()->detach();
            $facility->forceDelete();

            FacilityAuditLog::where('facility_id', $facilityId)
                ->update(['facility_name' => $facilityName]);
        });
    }

    public function permanentlyDeleteMeter(FacilityMeter $meter): void
    {
        $meter->forceDelete();
    }

    private function logFacilityAudit(Facility $facility, string $action, string $reason): void
    {
        FacilityAuditLog::create([
            'facility_id' => $facility->id,
            'facility_name' => $facility->name,
            'action' => $action,
            'reason' => $reason,
            'performed_by' => auth()->id(),
        ]);
    }
}
