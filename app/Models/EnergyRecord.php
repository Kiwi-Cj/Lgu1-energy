<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyRecord extends Model
{
    use HasFactory;

    protected $table = 'energy_records';
    protected $fillable = [
        'facility_id',
        'month',
        'year',
        'kwh_consumed',
        'status',
        'created_by',
        'created_at',
        'meralco_bill',
        'kwh_vs_avg',
        'percent_change',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
