<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'facility_name',
        'action',
        'reason',
        'performed_by',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
