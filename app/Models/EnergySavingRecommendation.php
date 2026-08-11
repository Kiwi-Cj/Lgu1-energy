<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnergySavingRecommendation extends Model
{
    protected $fillable = [
        'facility_id', 'year', 'month', 'generated_message', 'engineer_recommendation',
        'status', 'expected_savings_kwh', 'target_date', 'reviewed_by', 'reviewed_at',
        'assigned_to', 'implementation_status', 'actual_savings_kwh', 'implementation_notes',
        'implemented_at', 'verified_by', 'verified_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'expected_savings_kwh' => 'decimal:2',
        'target_date' => 'date',
        'reviewed_at' => 'datetime',
        'actual_savings_kwh' => 'decimal:2',
        'implemented_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function facility() { return $this->belongsTo(Facility::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function verifier() { return $this->belongsTo(User::class, 'verified_by'); }
}
