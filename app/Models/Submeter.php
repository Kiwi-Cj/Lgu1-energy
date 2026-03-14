<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submeter extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'submeter_name',
        'meter_type',
        'status',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function readings()
    {
        return $this->hasMany(SubmeterReading::class);
    }

    public function baselines()
    {
        return $this->hasMany(SubmeterBaseline::class);
    }

    public function alerts()
    {
        return $this->hasMany(SubmeterAlert::class);
    }

    public function equipments()
    {
        return $this->hasMany(SubmeterEquipment::class);
    }
}
