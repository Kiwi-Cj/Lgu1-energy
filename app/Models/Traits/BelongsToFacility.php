<?php
namespace App\Models\Traits;

trait BelongsToFacility
{
    public function facility()
    {
        return $this->belongsTo(\App\Models\Facility::class, 'facility_id');
    }
}
