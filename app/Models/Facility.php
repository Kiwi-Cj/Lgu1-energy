<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    public function maintenance()
    {
        return $this->hasMany(\App\Models\Maintenance::class, 'facility_id');
    }
    use HasFactory;

    protected $table = 'facilities';
    protected $fillable = [
        'name',
        'type',
        'department',
        'address',
        'barangay',
        'floor_area',
        'floors',
        'year_built',
        'operating_hours',
        'status',
        'image',
    ];
    public function energyProfiles()
    {
        return $this->hasMany(EnergyProfile::class);
    }

    public function energyRecords()
    {
        return $this->hasMany(EnergyRecord::class);
    }
}
