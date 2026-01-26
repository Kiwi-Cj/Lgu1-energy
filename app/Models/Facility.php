<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Facility extends Model
{
    use HasFactory;

    protected $table = 'facilities';

    protected $fillable = [
        'name',
        'type',
        'size', // DB size (manual / optional)
        'department',
        'address',
        'barangay',
        'floor_area',
        'floors',
        'year_built',
        'operating_hours',
        'status',
        'image',
        'baseline_status',
        'baseline_kwh',
        'baseline_start_date',
    ];

    /* =======================
     | RELATIONSHIPS
     ======================= */

    public function maintenance()
    {
        return $this->hasMany(\App\Models\Maintenance::class, 'facility_id');
    }

    public function energyReadings()
    {
        return $this->hasMany(\App\Models\EnergyReading::class);
    }

    public function energyProfiles()
    {
        return $this->hasMany(\App\Models\EnergyProfile::class);
    }

    public function energyRecords()
    {
        return $this->hasMany(\App\Models\EnergyRecord::class);
    }

    /* =======================
     | COMPUTED ATTRIBUTES
     ======================= */

    /**
     * Efficiency (%) = (Baseline kWh / Current Month kWh) × 100
     */
    public function getEfficiencyPercentAttribute()
    {
        if ($this->baseline_status !== 'active' || !$this->baseline_kwh) {
            return null;
        }

        $now = now();

        $reading = $this->energyReadings()
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->first();

        if (!$reading || $reading->kwh <= 0) {
            return null;
        }

        return round(($this->baseline_kwh / $reading->kwh) * 100, 2);
    }

    /**
     * Consumption Status based on efficiency
     */
    public function getConsumptionStatusAttribute()
    {
        $eff = $this->efficiency_percent;

        if ($eff === null) return 'NO DATA';
        if ($eff >= 100) return 'EFFICIENT';
        if ($eff >= 80) return 'WARNING';

        return 'CRITICAL';
    }

    /**
     * Computed facility size based on average monthly kWh
     * (avoids conflict with DB column `size`)
     */
    public function getComputedSizeAttribute()
    {
        $baseline = $this->average_monthly_kwh ?? 0;

        if ($baseline < 1500) {
            return 'SMALL';
        } elseif ($baseline < 3000) {
            return 'MEDIUM';
        } elseif ($baseline < 6000) {
            return 'LARGE';
        } else {
            return 'EXTRA_LARGE';
        }
    }

    /**
     * Computed average monthly kWh from latest EnergyProfile
     */
    public function getAverageMonthlyKwhAttribute()
    {
        // Always use first3months_data if available, even if DB value is zero
        $first3mo = \DB::table('first3months_data')->where('facility_id', $this->id)->first();
        if ($first3mo) {
            $avg = (floatval($first3mo->month1) + floatval($first3mo->month2) + floatval($first3mo->month3)) / 3;
            return round($avg, 2);
        }
        $profile = $this->energyProfiles()->latest()->first();
        return $profile ? $profile->average_monthly_kwh : 0;
    }

    /* =======================
     | BUSINESS LOGIC
     ======================= */

    /**
     * Auto-update latest EnergyProfile average
     * when 3 records exist
     */
    public function updateProfileAverageFromRecords()
    {
        $records = $this->energyRecords()
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->take(3)
            ->get();

        if ($records->count() === 3) {
            $avg = $records->avg('actual_kwh');

            $profile = $this->energyProfiles()->latest()->first();
            if ($profile) {
                $profile->update([
                    'average_monthly_kwh' => $avg
                ]);
            }
        }
    }

    /**
     * Check if facility has high consumption this month
     */
    public function isHighConsumption()
    {
        if ($this->baseline_status !== 'active') return false;

        $now = Carbon::now();

        $reading = $this->energyReadings()
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->first();

        if (!$reading) return false;

        return $reading->kwh > ($this->baseline_kwh * 1.2);
    }
}
