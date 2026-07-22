<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyEnergyChecklist extends Model
{
    protected $fillable = [
        'facility_id', 'checklist_date', 'task_key', 'task_label', 'period',
        'is_completed', 'completed_by', 'completed_at',
    ];

    protected $casts = [
        'checklist_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function facility() { return $this->belongsTo(Facility::class); }
    public function completedBy() { return $this->belongsTo(User::class, 'completed_by'); }
}
