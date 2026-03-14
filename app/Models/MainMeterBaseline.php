<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainMeterBaseline extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'baseline_type',
        'baseline_kwh',
        'baseline_kwh_per_day',
        'baseline_peak_kw',
        'computed_for_period',
        'computed_at',
    ];

    protected $casts = [
        'baseline_kwh' => 'decimal:2',
        'baseline_kwh_per_day' => 'decimal:4',
        'baseline_peak_kw' => 'decimal:2',
        'computed_at' => 'datetime',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
