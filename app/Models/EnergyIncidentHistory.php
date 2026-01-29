<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyIncidentHistory extends Model
{
    use HasFactory;

    protected $table = 'energy_incident_histories';

    protected $fillable = [
        'energy_record_id',
        'alert_level',
        'deviation',
        'date_detected',
        'status',
    ];

    public function energyRecord()
    {
        return $this->belongsTo(EnergyRecord::class);
    }

    /**
     * Log a new High alert incident.
     *
     * @param int $energyRecordId
     * @param float $deviation
     * @return static
     */
    public static function logHighAlert($energyRecordId, $deviation)
    {
        return self::create([
            'energy_record_id' => $energyRecordId,
            'alert_level' => 'High',
            'deviation' => $deviation,
            'date_detected' => now()->toDateString(),
            'status' => 'Open',
        ]);
    }
}
