<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToFacility;

class Maintenance extends Model
{
    use HasFactory;

    protected $table = 'maintenance';
    protected $fillable = [
        'facility_id',
        'energy_record_id',
        'energy_incident_id',
        'issue_type',
        'trigger_month',
        'trend',
        'maintenance_type',
        'maintenance_status',
        'scheduled_date',
        'assigned_to',
        'completed_date',
        'proof_photo_path',
        'photo_requirement',
        'remarks',
    ];


    use BelongsToFacility;

    public function energyRecord()
    {
        return $this->belongsTo(EnergyRecord::class, 'energy_record_id');
    }

    public function energyIncident()
    {
        return $this->belongsTo(EnergyIncident::class, 'energy_incident_id');
    }

    /**
     * Older maintenance rows can be linked through their energy record rather
     * than directly through facility_id. Use that link before showing a
     * generic label in alerts and notifications.
     */
    public function resolvedFacilityName(): string
    {
        $this->loadMissing('facility:id,name', 'energyRecord.facility:id,name', 'energyIncident.facility:id,name');

        $name = trim((string) ($this->facility?->name ?? ''));
        if ($name === '') {
            $name = trim((string) ($this->energyRecord?->facility?->name ?? ''));
        }
        if ($name === '') {
            $name = trim((string) ($this->energyIncident?->facility?->name ?? ''));
        }

        return $name !== '' ? $name : 'Facility not linked';
    }
}
