<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessageReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_message_id',
        'sent_by_user_id',
        'recipient_email',
        'subject',
        'message',
        'attachments',
        'send_status',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'attachments' => 'array',
        'sent_at' => 'datetime',
    ];

    public function contactMessage()
    {
        return $this->belongsTo(ContactMessage::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }
}
