<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyEnergyChecklistTask extends Model
{
    protected $fillable = ['facility_id', 'task_key', 'task_label', 'period', 'is_active', 'created_by'];
    protected $casts = ['is_active' => 'boolean'];

    public function facility() { return $this->belongsTo(Facility::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
