<?php

namespace App\Mail;

use App\Support\SystemSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public string $systemName;

    public string $systemShortName;

    public string $organizationName;

    public function __construct(
        public string $recipientName,
        public string $recipientEmail,
        public string $role,
        public string $temporaryPassword,
        public string $loginUrl,
    ) {
        $this->systemName = SystemSettings::string('system_name', 'LGU Energy Monitoring System');
        $this->systemShortName = SystemSettings::string('short_name', 'LGU EMS');
        $this->organizationName = SystemSettings::string('org_name', 'Local Government Unit');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your {$this->systemShortName} Account Is Ready",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-welcome',
            text: 'emails.user-welcome-text',
        );
    }
}
