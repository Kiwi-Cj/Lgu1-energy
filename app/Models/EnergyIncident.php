<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyIncident extends Model
{
    use HasFactory;

    protected $table = 'enrgy_incident';

    protected $fillable = [
        'facility_id',
        'description',
        'status',
        'date_detected',
        'created_by',
        'resolved_at',
    ];
}
