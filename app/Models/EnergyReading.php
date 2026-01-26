<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'month',
        'year',
        'kwh',
        'is_baseline_data',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
