<?php

namespace App\Models;

use App\Models\Traits\BelongsToFacility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyChecklistItem extends Model
{
    use HasFactory;
    use BelongsToFacility;

    protected $table = 'daily_checklist_items';

    protected $fillable = [
        'facility_id',
        'issue_type',
        'trigger_month',
        'maintenance_status',
        'scheduled_date',
        'assigned_to',
        'completed_date',
        'proof_photo_path',
        'photo_requirement',
        'remarks',
    ];
}
