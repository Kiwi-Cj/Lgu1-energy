<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToFacility;

class EnergyRecord extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(function ($record) {
            if ($record->facility) {
                $record->facility->updateProfileAverageFromRecords();
            }
        });
    }

    protected $table = 'energy_records';
    protected $fillable = [
        'facility_id',
        'year',
        'month',
        'actual_kwh',
        'baseline_kwh',
        'deviation_percent',
        'alert_level',
        'alert_message',
        'energy_cost',
        'rate_per_kwh',
        'recorded_by',
        'meralco_bill_picture', // optional bill image
    ];


    use BelongsToFacility;

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Calculate alert flag based on 10% above average
    public function calculateAlertFlag()
    {
        $avg = $this->facility->average_monthly_kwh ?? 0;
        $threshold = $avg * 1.1;
        return $this->actual_kwh > $threshold ? 1 : 0;
    }

    // kWh vs average
    public function getKwhVsAvgAttribute()
    {
        $avg = $this->facility->average_monthly_kwh ?? 0;
        return $this->actual_kwh - $avg;
    }

    // Percent change from previous month
    public function getPercentChangeAttribute()
    {
        $prev = $this->getPreviousMonthKwh();
        if ($prev && $prev != 0) {
            return round((($this->actual_kwh - $prev) / $prev) * 100, 2);
        }
        return null;
    }

    // Helper: get previous month kWh
    public function getPreviousMonthKwh()
    {
        $prevMonth = $this->month == 1 ? 12 : $this->month - 1;
        $prevYear = $this->month == 1 ? $this->year - 1 : $this->year;
        $prev = self::where('facility_id', $this->facility_id)
            ->where('month', $prevMonth)
            ->where('year', $prevYear)
            ->first();
        return $prev ? $prev->actual_kwh : null;
    }

    // Status logic
    public function getStatusLabelAttribute()
    {
        if ($this->reset_reason) return 'reset';
        if ($this->engineer_approved) return 'approved';
        return $this->status ?? 'active';
    }
}
