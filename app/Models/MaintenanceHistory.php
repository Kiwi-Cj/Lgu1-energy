<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToFacility;

class MaintenanceHistory extends Model
{
    use HasFactory;
    protected $table = 'maintenance_history';
    protected $fillable = [
        'facility_id',
        'issue_type',
        'trigger_month',
        'trend',
        'efficiency_rating',
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

}
