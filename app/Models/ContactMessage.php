<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'ip_address',
        'user_agent',
        'mailed_to',
        'emailed_at',
        'email_error',
    ];

    protected $casts = [
        'emailed_at' => 'datetime',
    ];
}
