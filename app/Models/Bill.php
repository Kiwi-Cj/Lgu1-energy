<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'month',
        'kwh_consumed',
        'unit_cost',
        'total_bill',
        'status',
        'meralco_bill_picture',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    // Accessor for total_bill
    public function getTotalBillAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }
        // Compute if not set
        return $this->kwh_consumed * $this->unit_cost;
    }
}
