<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class Facility extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Many-to-many: Facility can have many users
    public function users()
    {
        return $this->belongsToMany(User::class, 'facility_user', 'facility_id', 'user_id');
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

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
        'deleted_by',
        'archive_reason',
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

    public function meters()
    {
        return $this->hasMany(FacilityMeter::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(FacilityAuditLog::class);
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
        $label = static::resolveSizeLabelFromBaseline($this->baseline_kwh);

        if (! $label) {
            return 'N/A';
        }

        return strtoupper(str_replace([' ', '-'], '_', $label));
    }

    /**
     * Resolve facility size category from baseline kWh using configured operational ranges.
     */
    public static function resolveSizeLabelFromBaseline($baseline): ?string
    {
        if (! is_numeric($baseline) || (float) $baseline <= 0) {
            return null;
        }

        $baseline = (float) $baseline;

        if ($baseline < 3000) {
            return 'Small';
        }

        if ($baseline < 10000) {
            return 'Medium';
        }

        if ($baseline < 30000) {
            return 'Large';
        }

        return 'Extra Large';
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

    // Removed auto-update of baseline_kwh. Baseline is now fixed and only updated manually.

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
        // first3months_data table removed; fallback to baseline_kwh
        return $value;
    }

    public function getResolvedImageUrlAttribute(): ?string
    {
        $candidates = [];

        if (!empty($this->image_path)) {
            $candidates[] = ltrim((string) $this->image_path, '/');
        }

        if (!empty($this->image)) {
            $candidates[] = ltrim((string) $this->image, '/');
        }

        foreach ($candidates as $path) {
            if ($path === '') {
                continue;
            }

            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            if (str_starts_with($path, 'img/') || str_starts_with($path, 'uploads/') || str_starts_with($path, 'storage/')) {
                return asset($path);
            }

            if (Storage::disk('public')->exists($path)) {
                return asset('storage/' . $path);
            }
        }

        return null;
    }
}
