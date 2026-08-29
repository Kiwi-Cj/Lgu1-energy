<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmeterEquipment extends Model
{
    use HasFactory;

    protected $table = 'submeter_equipments';

    protected $fillable = [
        'facility_id',
        'meter_scope',
        'submeter_id',
        'facility_meter_id',
        'equipment_name',
        'category',
        'location',
        'quantity',
        'rated_watts',
        'operating_hours_per_day',
        'operating_days_per_month',
        'notes',
    ];

    protected $casts = [
        'facility_id' => 'integer',
        'meter_scope' => 'string',
        'quantity' => 'integer',
        'rated_watts' => 'decimal:2',
        'operating_hours_per_day' => 'decimal:2',
        'operating_days_per_month' => 'integer',
        'estimated_kwh' => 'decimal:2',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function submeter(): BelongsTo
    {
        return $this->belongsTo(Submeter::class);
    }

    public function mainMeter(): BelongsTo
    {
        return $this->belongsTo(FacilityMeter::class, 'facility_meter_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(SubmeterEquipmentFile::class, 'submeter_equipment_id');
    }

    public function getTotalWattsAttribute(): float
    {
        $watts = is_numeric($this->rated_watts) ? (float) $this->rated_watts : 0.0;
        $quantity = is_numeric($this->quantity) ? (int) $this->quantity : 0;

        return round($watts * max(0, $quantity), 2);
    }

    public function getTotalKwAttribute(): float
    {
        return round($this->total_watts / 1000, 3);
    }

    public function getDailyKwhAttribute(): float
    {
        $watts = is_numeric($this->rated_watts) ? (float) $this->rated_watts : 0.0;
        $quantity = is_numeric($this->quantity) ? (int) $this->quantity : 0;
        $hours = is_numeric($this->operating_hours_per_day) ? (float) $this->operating_hours_per_day : 0.0;

        return round(($watts * max(0, $quantity) * max(0, $hours)) / 1000, 2);
    }

    public function getMonthlyKwhAttribute(): float
    {
        $watts = is_numeric($this->rated_watts) ? (float) $this->rated_watts : 0.0;
        $quantity = is_numeric($this->quantity) ? (int) $this->quantity : 0;
        $hours = is_numeric($this->operating_hours_per_day) ? (float) $this->operating_hours_per_day : 0.0;
        $days = is_numeric($this->operating_days_per_month) ? (int) $this->operating_days_per_month : 0;

        return round(($watts * max(0, $quantity) * max(0, $hours) * max(0, $days)) / 1000, 2);
    }

    public function getMeterScopeLabelAttribute(): string
    {
        return strtolower((string) $this->meter_scope) === 'main' ? 'Main Meter' : 'Sub Meter';
    }

    public function getMeterNameAttribute(): string
    {
        if (strtolower((string) $this->meter_scope) === 'main') {
            return (string) ($this->mainMeter?->meter_name ?? 'Main Meter');
        }

        return (string) ($this->submeter?->submeter_name ?? 'Submeter');
    }
}
