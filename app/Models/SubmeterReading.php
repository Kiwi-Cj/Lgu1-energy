<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmeterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'submeter_id',
        'period_type',
        'period_start_date',
        'period_end_date',
        'reading_start_kwh',
        'reading_end_kwh',
        'operating_days',
        'encoded_by_user_id',
        'approved_by_engineer_id',
        'approved_at',
    ];

    protected $casts = [
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'approved_at' => 'datetime',
        'reading_start_kwh' => 'decimal:2',
        'reading_end_kwh' => 'decimal:2',
        'kwh_used' => 'decimal:2',
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

    public function scopeForPeriodType(Builder $query, string $periodType): Builder
    {
        return $query->where('period_type', $periodType);
    }

    public function submeter()
    {
        return $this->belongsTo(Submeter::class);
    }

    public function encodedBy()
    {
        return $this->belongsTo(User::class, 'encoded_by_user_id');
    }

    public function approvedByEngineer()
    {
        return $this->belongsTo(User::class, 'approved_by_engineer_id');
    }

    public function alert()
    {
        return $this->hasOne(SubmeterAlert::class, 'submeter_reading_id');
    }

    public function periodLabel(): string
    {
        $endDate = $this->period_end_date instanceof Carbon
            ? $this->period_end_date
            : Carbon::parse($this->period_end_date);

        if ($this->period_type === 'monthly') {
            return $endDate->format('Y-m');
        }

        if ($this->period_type === 'weekly') {
            return sprintf('%s-W%s', $endDate->format('o'), $endDate->format('W'));
        }

        return $endDate->format('Y-m-d');
    }
}

