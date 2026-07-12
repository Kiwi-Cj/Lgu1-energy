<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'badge_color',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];
}
