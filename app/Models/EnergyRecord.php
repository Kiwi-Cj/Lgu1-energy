<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToFacility;

class EnergyRecord extends Model
{
    // Set alert value based on deviation and baseline
    public function setAlertAttribute($value)
    {
        $baseline = $this->baseline_kwh ?? 0;
        $deviation = $this->deviation;
        $size = 'Medium';
        if ($baseline <= 1000) {
            $size = 'Small';
        } elseif ($baseline <= 3000) {
            $size = 'Medium';
        } elseif ($baseline <= 10000) {
            $size = 'Large';
        } else {
            $size = 'Extra Large';
        }
        $thresholds = [
            'Small' =>    [ 'level5' => 80,  'level4' => 50,  'level3' => 30,  'level2' => 15 ],
            'Medium' =>   [ 'level5' => 60,  'level4' => 40,  'level3' => 20,  'level2' => 10 ],
            'Large' =>    [ 'level5' => 30,  'level4' => 20,  'level3' => 12,  'level2' => 5  ],
            'Extra Large'=>[ 'level5' => 20,  'level4' => 12,  'level3' => 7,   'level2' => 3  ],
        ];
        $t = $thresholds[$size];
        if ($deviation === null) {
            $this->attributes['alert'] = '';
        } elseif ($deviation > $t['level5']) {
            $this->attributes['alert'] = 'Extreme / level 5';
        } elseif ($deviation > $t['level4']) {
            $this->attributes['alert'] = 'Extreme / level 4';
        } elseif ($deviation > $t['level3']) {
            $this->attributes['alert'] = 'High / level 3';
        } elseif ($deviation > $t['level2']) {
            $this->attributes['alert'] = 'Warning / level 2';
        } else {
            $this->attributes['alert'] = 'Normal / Low';
        }
    }
// ...existing code...
    // Deviation percentage from baseline kWh
    public function getDeviationAttribute()
    {
        $baseline = $this->baseline_kwh ?? 0;
        if ($baseline > 0) {
            return round((($this->actual_kwh - $baseline) / $baseline) * 100, 2);
        }
        return null;
    }
    use HasFactory;

    // Removed auto-update of baseline_kwh in EnergyRecord booted event.

    protected $table = 'energy_records';
    protected $fillable = [
        'facility_id',
        'year',
        'month',
        'day',
        'actual_kwh',
        'energy_cost',
        'rate_per_kwh',
        'recorded_by',
        'bill_image', // optional bill image
        'baseline_kwh',
        'deviation',
        'alert',
    ];


    use BelongsToFacility;

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Calculate alert flag based on 10% above average
    public function calculateAlertFlag()
    {
        $avg = $this->facility->baseline_kwh ?? 0;
        $threshold = $avg * 1.1;
        return $this->actual_kwh > $threshold ? 1 : 0;
    }

    // kWh vs average
    public function getKwhVsAvgAttribute()
    {
        $avg = $this->facility->baseline_kwh ?? 0;
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

