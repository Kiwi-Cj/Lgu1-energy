<?php

namespace App\Models;

use App\Models\Traits\BelongsToFacility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityMeterWeeklyReading extends Model
{
    use HasFactory;
    use BelongsToFacility;

    protected $table = 'facility_meter_weekly_readings';

    protected $fillable = [
        'facility_id',
        'meter_id',
        'year',
        'month',
        'week_number',
        'reading_date',
        'previous_reading_kwh',
        'current_reading_kwh',
        'actual_kwh',
        'rate_per_kwh',
        'cost',
        'notes',
        'encoded_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'week_number' => 'integer',
        'reading_date' => 'date',
        'previous_reading_kwh' => 'decimal:2',
        'current_reading_kwh' => 'decimal:2',
        'actual_kwh' => 'decimal:2',
        'rate_per_kwh' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function meter()
    {
        return $this->belongsTo(FacilityMeter::class, 'meter_id');
    }

    public function encodedBy()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    /**
     * Get label for week period (e.g. Days 1–7)
     */
    public function getWeekLabelAttribute(): string
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int) $this->month, (int) $this->year);
        return match ((int) $this->week_number) {
            1 => 'Days 1–7',
            2 => 'Days 8–14',
            3 => 'Days 15–21',
            4 => "Days 22–{$daysInMonth}",
            default => 'Week ' . $this->week_number,
        };
    }
}
