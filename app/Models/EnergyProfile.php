<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToFacility;

class EnergyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'primary_meter_id',
        'electric_meter_no',
        'utility_provider',
        'contract_account_no',
        'baseline_kwh',
        'main_energy_source',
        'backup_power',
        'transformer_capacity',
        'number_of_meters',
        'bill_image', // allow saving bill_image if present
        'baseline_source',
    ];


    use BelongsToFacility;

    public function primaryMeter()
    {
        return $this->belongsTo(FacilityMeter::class, 'primary_meter_id');
    }
}
