<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmeterEquipmentFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'submeter_equipment_id',
        'meter_scope',
        'submeter_id',
        'facility_meter_id',
        'original_name',
        'stored_name',
        'storage_path',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    protected $casts = [
        'submeter_equipment_id' => 'integer',
        'submeter_id' => 'integer',
        'facility_meter_id' => 'integer',
        'file_size' => 'integer',
        'uploaded_by' => 'integer',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(SubmeterEquipment::class, 'submeter_equipment_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

