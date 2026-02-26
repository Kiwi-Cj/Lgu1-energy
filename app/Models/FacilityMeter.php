<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacilityMeter extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'facility_id',
        'meter_name',
        'meter_number',
        'meter_type',
        'parent_meter_id',
        'location',
        'status',
        'multiplier',
        'baseline_kwh',
        'notes',
        'deleted_by',
        'archive_reason',
    ];

    protected $casts = [
        'multiplier' => 'decimal:4',
        'baseline_kwh' => 'decimal:2',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function parentMeter()
    {
        return $this->belongsTo(self::class, 'parent_meter_id');
    }

    public function childMeters()
    {
        return $this->hasMany(self::class, 'parent_meter_id');
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
