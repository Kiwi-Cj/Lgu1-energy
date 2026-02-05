<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnergyIncident extends Model
{
    protected $fillable = [
        'incident_code',
        'facility_id',
        'energy_record_id',
        'month',
        'year',
        'incident_type',
        'severity_level',
        'date_detected',
        'time_detected',
        'detected_by',
        'current_consumption',
        'previous_consumption',
        'deviation_percent',
        'threshold_exceeded',
        'billing_period',
        'description',
        'probable_cause',
        'immediate_action',
        'linked_action_id',
        'assigned_officer_id',
        'escalated_to_id',
        'escalation_date',
        'status',
        'attachments',
        'date_resolved',
        'resolution_summary',
    ];

    protected $casts = [
        'attachments' => 'array',
        'date_detected' => 'date',
        'time_detected' => 'datetime:H:i',
        'escalation_date' => 'datetime',
        'date_resolved' => 'date',
        'probable_cause' => 'array',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
    public function assignedOfficer()
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }
    public function escalatedTo()
    {
        return $this->belongsTo(User::class, 'escalated_to_id');
    }
}
