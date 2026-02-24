<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage)
    {
    }

    public function envelope(): Envelope
    {
        $subjectSuffix = $this->contactMessage->subject
            ? ': ' . $this->contactMessage->subject
            : '';

        return new Envelope(
            subject: 'New Contact Form Message' . $subjectSuffix,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-message-received',
        );
    }
}
