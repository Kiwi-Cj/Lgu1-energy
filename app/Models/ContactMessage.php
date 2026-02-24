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
        'read_at',
        'read_by_user_id',
    ];

    protected $casts = [
        'emailed_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function replies()
    {
        return $this->hasMany(ContactMessageReply::class)->latest();
    }

    public function readBy()
    {
        return $this->belongsTo(User::class, 'read_by_user_id');
    }
}
