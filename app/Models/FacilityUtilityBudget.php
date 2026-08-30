<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityUtilityBudget extends Model
{
    use HasFactory;

    protected $table = 'facility_utility_budgets';

    protected $fillable = [
        'facility_id',
        'fiscal_year',
        'annual_budget_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'facility_id' => 'integer',
        'fiscal_year' => 'integer',
        'annual_budget_amount' => 'decimal:2',
        'created_by' => 'integer',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get monthly allocated budget (Annual / 12).
     */
    public function getMonthlyBudgetAttribute(): float
    {
        $annual = (float) $this->annual_budget_amount;
        return round($annual / 12, 2);
    }
}
