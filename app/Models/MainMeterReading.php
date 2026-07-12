<?php

namespace App\Models;

use App\Models\Traits\BelongsToFacility;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainMeterReading extends Model
{
    use HasFactory;
    use BelongsToFacility;

    protected $table = 'main_meter_readings';

    protected $fillable = [
        'facility_id',
        'period_type',
        'period_start_date',
        'period_end_date',
        'reading_start_kwh',
        'reading_end_kwh',
        'operating_days',
        'input_source',
        'device_id',
        'received_at',
        'encoded_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'reading_start_kwh' => 'decimal:2',
        'reading_end_kwh' => 'decimal:2',
        'received_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

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

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function getKwhUsedAttribute(): ?float
    {
        if ($this->reading_start_kwh === null || $this->reading_end_kwh === null) {
            return null;
        }

        return round((float) $this->reading_end_kwh - (float) $this->reading_start_kwh, 2);
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
