<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnergyIncident extends Model
{
    protected $fillable = [
        'energy_record_id',
        'facility_id',
        'month',
        'year',
        'deviation_percent',
        'description',
        'status',
        'date_detected',
        'created_by',
        'resolved_at',
    ];

    protected $casts = [
        'date_detected' => 'date',
        'resolved_at' => 'datetime',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function energyRecord()
    {
        return $this->belongsTo(EnergyRecord::class, 'energy_record_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function classifySeverity(?float $deviation, ?float $baseline): array
    {
        if ($deviation === null) {
            return ['key' => 'normal', 'label' => 'Normal'];
        }

        $baseline = $baseline ?? 0.0;
        if ($baseline <= 0) {
            if ($deviation > 60) {
                return ['key' => 'critical', 'label' => 'Critical'];
            }
            if ($deviation > 40) {
                return ['key' => 'very-high', 'label' => 'Very High'];
            }
            if ($deviation > 20) {
                return ['key' => 'high', 'label' => 'High'];
            }
            if ($deviation > 10) {
                return ['key' => 'warning', 'label' => 'Warning'];
            }
            return ['key' => 'normal', 'label' => 'Normal'];
        }

        if ($baseline <= 1000) {
            $thresholds = ['level5' => 80, 'level4' => 50, 'level3' => 30, 'level2' => 15];
        } elseif ($baseline <= 3000) {
            $thresholds = ['level5' => 60, 'level4' => 40, 'level3' => 20, 'level2' => 10];
        } elseif ($baseline <= 10000) {
            $thresholds = ['level5' => 30, 'level4' => 20, 'level3' => 12, 'level2' => 5];
        } else {
            $thresholds = ['level5' => 20, 'level4' => 12, 'level3' => 7, 'level2' => 3];
        }

        if ($deviation > $thresholds['level5']) {
            return ['key' => 'critical', 'label' => 'Critical'];
        }
        if ($deviation > $thresholds['level4']) {
            return ['key' => 'very-high', 'label' => 'Very High'];
        }
        if ($deviation > $thresholds['level3']) {
            return ['key' => 'high', 'label' => 'High'];
        }
        if ($deviation > $thresholds['level2']) {
            return ['key' => 'warning', 'label' => 'Warning'];
        }

        return ['key' => 'normal', 'label' => 'Normal'];
    }

    public function getSeverityKeyAttribute($value): string
    {
        if (is_string($value) && trim($value) !== '') {
            return strtolower(trim($value));
        }

        $severity = $this->resolveSeverity();
        return $severity['key'];
    }

    public function getSeverityLabelAttribute($value): string
    {
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        $severity = $this->resolveSeverity();
        return $severity['label'];
    }

    protected function resolveSeverity(): array
    {
        $baseline = $this->energyRecord?->baseline_kwh
            ?? $this->facility?->baseline_kwh
            ?? null;

        return self::classifySeverity(
            $this->deviation_percent !== null ? (float) $this->deviation_percent : null,
            $baseline !== null ? (float) $baseline : null
        );
    }
}
