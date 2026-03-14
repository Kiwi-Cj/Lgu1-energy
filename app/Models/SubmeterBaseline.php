<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmeterBaseline extends Model
{
    use HasFactory;

    protected $fillable = [
        'submeter_id',
        'baseline_type',
        'months_window',
        'baseline_value_kwh',
        'baseline_value_normalized',
        'computed_for_period',
        'computed_at',
    ];

    protected $casts = [
        'baseline_value_kwh' => 'decimal:2',
        'baseline_value_normalized' => 'decimal:4',
        'computed_at' => 'datetime',
    ];

    public function submeter()
    {
        return $this->belongsTo(Submeter::class);
    }
}

