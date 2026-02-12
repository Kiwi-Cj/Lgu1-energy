<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Facility extends Model
{
    use HasFactory;

    // Many-to-many: Facility can have many users
    public function users()
    {
        return $this->belongsToMany(User::class, 'facility_user', 'facility_id', 'user_id');
    }
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
        'image_path',
        'baseline_status',
        'baseline_kwh',
        'baseline_start_date',
        'engineer_approved', // <-- Added for engineer approval
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
    * Computed facility size based on baseline kWh
    * (avoids conflict with DB column `size`)
     */
    public function getComputedSizeAttribute()
    {
        $baseline = $this->baseline_kwh ?? 0;

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

    // Removed: getAverageMonthlyKwhAttribute (use baseline_kwh instead)

    /**
     * Get engineer approval status for facility (from its own column).
     */
    public function getEngineerApprovedAttribute()
    {
        return (bool) $this->attributes['engineer_approved'] ?? false;
    }

    /* =======================
     | BUSINESS LOGIC
     ======================= */

    /**
     * Auto-update latest EnergyProfile aMAverage
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
                    'baseline_kwh' => $avg
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

    /**
     * Always get baseline_kwh from first 3 months data if available, else fallback to DB column
     */
    public function getBaselineKwhAttribute($value)
    {
        $first3mo = \DB::table('first3months_data')->where('facility_id', $this->id)->first();
        if ($first3mo && is_numeric($first3mo->month1) && is_numeric($first3mo->month2) && is_numeric($first3mo->month3)
            && $first3mo->month1 > 0 && $first3mo->month2 > 0 && $first3mo->month3 > 0) {
            return (floatval($first3mo->month1) + floatval($first3mo->month2) + floatval($first3mo->month3)) / 3;
        }
        return $value;
    }
}
