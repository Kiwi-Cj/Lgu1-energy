<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainMeterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'period_type',
        'period_start_date',
        'period_end_date',
        'reading_start_kwh',
        'reading_end_kwh',
        'operating_days',
        'peak_demand_kw',
        'power_factor',
        'encoded_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'approved_at' => 'datetime',
        'reading_start_kwh' => 'decimal:2',
        'reading_end_kwh' => 'decimal:2',
        'kwh_used' => 'decimal:2',
        'peak_demand_kw' => 'decimal:2',
        'power_factor' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $reading) {
            if ($reading->period_start_date && $reading->period_end_date) {
                $start = Carbon::parse($reading->period_start_date);
                $end = Carbon::parse($reading->period_end_date);
                if ($start->greaterThan($end)) {
                    throw new \InvalidArgumentException('Period start date cannot be later than period end date.');
                }
            }

            if ($reading->reading_end_kwh !== null && $reading->reading_start_kwh !== null
                && (float) $reading->reading_end_kwh < (float) $reading->reading_start_kwh
            ) {
                throw new \InvalidArgumentException('Ending kWh reading must be greater than or equal to starting kWh.');
            }
        });
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereNotNull('approved_at');
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function encodedBy()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function alert()
    {
        return $this->hasOne(MainMeterAlert::class, 'main_meter_reading_id');
    }

    public function periodLabel(): string
    {
        $endDate = $this->period_end_date instanceof Carbon
            ? $this->period_end_date
            : Carbon::parse($this->period_end_date);

        return $endDate->format('Y-m');
    }
}
