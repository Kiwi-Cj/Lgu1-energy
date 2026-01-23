<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyEfficiency extends Model
{
    use HasFactory;
    protected $table = 'energy_efficiency';
    protected $fillable = [
        'facility_id', 'month', 'year', 'actual_kwh', 'avg_kwh', 'variance', 'eui', 'rating'
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class, 'facility_id');
    }
}
