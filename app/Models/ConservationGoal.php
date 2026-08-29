<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConservationGoal extends Model
{
    protected $fillable = [
        'facility_id', 'name', 'description', 'goal_type', 'target_metric',
        'target_value', 'baseline_value', 'baseline_start_date', 'baseline_end_date',
        'responsible_department', 'action_plan', 'start_date', 'end_date', 'status', 'created_by',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'baseline_value' => 'decimal:2',
        'baseline_start_date' => 'date',
        'baseline_end_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function facility() { return $this->belongsTo(Facility::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
