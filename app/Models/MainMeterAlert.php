<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainMeterAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_meter_reading_id',
        'facility_id',
        'baseline_kwh',
        'current_kwh',
        'increase_percent',
        'alert_level',
        'reason',
    ];

    protected $casts = [
        'baseline_kwh' => 'decimal:2',
        'current_kwh' => 'decimal:2',
        'increase_percent' => 'decimal:2',
    ];

    public function reading()
    {
        return $this->belongsTo(MainMeterReading::class, 'main_meter_reading_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
