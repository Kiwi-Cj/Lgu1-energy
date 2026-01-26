<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToFacility;

class EnergyEfficiency extends Model
{
    use HasFactory;
    protected $table = 'energy_efficiency';
    protected $fillable = [
        'facility_id', 'month', 'year', 'actual_kwh', 'avg_kwh', 'variance', 'eui', 'rating'
    ];


    use BelongsToFacility;
}
