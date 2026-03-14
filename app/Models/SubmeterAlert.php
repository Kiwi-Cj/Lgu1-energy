<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmeterAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'submeter_reading_id',
        'submeter_id',
        'baseline_value_kwh',
        'current_value_kwh',
        'increase_percent',
        'alert_level',
        'reason',
    ];

    protected $casts = [
        'baseline_value_kwh' => 'decimal:2',
        'current_value_kwh' => 'decimal:2',
        'increase_percent' => 'decimal:2',
    ];

    public function reading()
    {
        return $this->belongsTo(SubmeterReading::class, 'submeter_reading_id');
    }

    public function submeter()
    {
        return $this->belongsTo(Submeter::class);
    }
}

