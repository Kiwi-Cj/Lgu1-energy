<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $table = 'maintenance';
    protected $fillable = [
        'facility_id',
        'issue_type',
        'trigger_month',
        'efficiency_rating',
        'trend',
        'maintenance_type',
        'maintenance_status',
        'scheduled_date',
        'assigned_to',
        'completed_date',
        'remarks',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function energyEfficiency()
    {
        return $this->hasOne(EnergyEfficiency::class, 'facility_id', 'facility_id');
    }
}
