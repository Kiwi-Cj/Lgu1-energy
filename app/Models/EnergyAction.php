<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyAction extends Model
{
    use HasFactory;

    protected $table = 'energy_actions';

    protected $fillable = [
        'facility_id',
        'action_type',
        'description',
        'priority',
        'target_date',
        'status',
        'risk_score',
        'alert_level',
        'trigger_reason',
        'current_kwh',
        'baseline_kwh',
        'deviation',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
