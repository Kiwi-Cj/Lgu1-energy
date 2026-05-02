<?php

namespace App\Models;

use App\Models\Traits\BelongsToFacility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class EnergyRecord extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BelongsToFacility;

    private static ?array $thresholdCache = null;

    protected $table = 'energy_records';

    protected $fillable = [
        'facility_id',
        'meter_id',
        'year',
        'month',
        'day',
        'actual_kwh',
        'energy_cost',
        'rate_per_kwh',
        'recorded_by',
        'bill_image',
        'baseline_kwh',
        'deviation',
        'alert',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'day' => 'integer',
        'actual_kwh' => 'decimal:2',
        'energy_cost' => 'decimal:2',
        'rate_per_kwh' => 'decimal:2',
        'baseline_kwh' => 'decimal:2',
        'deviation' => 'decimal:2',
    ];

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // Backward-compatible alias for older code paths.
    public function creator()
    {
        return $this->recordedBy();
    }

    public function meter()
    {
        return $this->belongsTo(FacilityMeter::class, 'meter_id');
    }

    public function getDeviationAttribute($value): ?float
    {
        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        $actual = is_numeric($this->attributes['actual_kwh'] ?? null)
            ? (float) $this->attributes['actual_kwh']
            : null;
        $baseline = is_numeric($this->attributes['baseline_kwh'] ?? null)
            ? (float) $this->attributes['baseline_kwh']
            : null;

        return static::calculateDeviation($actual, $baseline);
    }

    public static function calculateDeviation(?float $actualKwh, ?float $baselineKwh): ?float
    {
        if ($actualKwh === null || $baselineKwh === null || $baselineKwh <= 0) {
            return null;
        }

        return round((($actualKwh - $baselineKwh) / $baselineKwh) * 100, 2);
    }

    public static function resolveAlertLevel(?float $deviation, ?float $baselineKwh, ?array $thresholds = null): string
    {
        if ($deviation === null) {
            return '';
        }

        $thresholds = $thresholds ?? static::alertThresholdsBySize();
        $sizeKey = static::resolveSizeKeyFromBaseline($baselineKwh);
        $t = $thresholds[$sizeKey] ?? $thresholds['small'];

        if ($deviation > (float) $t['level5']) {
            return 'Critical';
        }
        if ($deviation > (float) $t['level4']) {
            return 'Very High';
        }
        if ($deviation > (float) $t['level3']) {
            return 'High';
        }
        if ($deviation > (float) $t['level2']) {
            return 'Warning';
        }

        return 'Normal';
    }

    public static function resolveSizeKeyFromBaseline(?float $baselineKwh): string
    {
        $baseline = $baselineKwh ?? 0.0;
        if ($baseline <= 1000) {
            return 'small';
        }
        if ($baseline <= 3000) {
            return 'medium';
        }
        if ($baseline <= 10000) {
            return 'large';
        }

        return 'xlarge';
    }

    public static function alertThresholdsBySize(): array
    {
        if (self::$thresholdCache !== null) {
            return self::$thresholdCache;
        }

        $defaults = [
            'small' => ['level1' => 5, 'level2' => 10, 'level3' => 15, 'level4' => 25, 'level5' => 35],
            'medium' => ['level1' => 4, 'level2' => 8, 'level3' => 12, 'level4' => 20, 'level5' => 30],
            'large' => ['level1' => 3, 'level2' => 6, 'level3' => 10, 'level4' => 16, 'level5' => 24],
            'xlarge' => ['level1' => 2, 'level2' => 4, 'level3' => 7, 'level4' => 12, 'level5' => 18],
        ];

        try {
            if (! Schema::hasTable('settings')) {
                return self::$thresholdCache = $defaults;
            }

            $keys = [];
            foreach (array_keys($defaults) as $sizeKey) {
                for ($level = 1; $level <= 5; $level++) {
                    $keys[] = "alert_level{$level}_{$sizeKey}";
                }
            }

            $settings = Setting::getMany($keys);

            $resolved = [];
            foreach ($defaults as $sizeKey => $levels) {
                $resolved[$sizeKey] = [];
                foreach ($levels as $levelKey => $defaultValue) {
                    $settingKey = "alert_{$levelKey}_{$sizeKey}";
                    $raw = $settings[$settingKey] ?? $defaultValue;
                    $resolved[$sizeKey][$levelKey] = is_numeric($raw) ? (float) $raw : (float) $defaultValue;
                }
            }

            return self::$thresholdCache = $resolved;
        } catch (\Throwable $e) {
            return self::$thresholdCache = $defaults;
        }
    }

    // Percent change from previous month (same scope).
    public function getPercentChangeAttribute(): ?float
    {
        $previous = $this->getPreviousMonthKwh();
        $current = is_numeric($this->actual_kwh) ? (float) $this->actual_kwh : null;

        if ($current === null || $previous === null || $previous == 0.0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    public function getPreviousMonthKwh(): ?float
    {
        $month = (int) ($this->month ?? 0);
        $year = (int) ($this->year ?? 0);
        if ($month <= 0 || $year <= 0) {
            return null;
        }

        $previousMonth = $month === 1 ? 12 : $month - 1;
        $previousYear = $month === 1 ? $year - 1 : $year;

        $previous = self::query()
            ->where('facility_id', $this->facility_id)
            ->where('month', $previousMonth)
            ->where('year', $previousYear)
            ->when(
                $this->meter_id,
                fn ($query) => $query->where('meter_id', $this->meter_id),
                fn ($query) => $query->whereNull('meter_id')
            )
            ->first();

        return $previous && is_numeric($previous->actual_kwh)
            ? (float) $previous->actual_kwh
            : null;
    }
}

