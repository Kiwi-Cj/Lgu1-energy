<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnergySavingRecommendation extends Model
{
    protected $fillable = [
        'facility_id', 'year', 'month', 'generated_message', 'engineer_recommendation',
        'status', 'expected_savings_kwh', 'target_date', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'expected_savings_kwh' => 'decimal:2',
        'target_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function facility() { return $this->belongsTo(Facility::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
