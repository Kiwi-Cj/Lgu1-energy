<?php


namespace App\Notifications;

use App\Support\SystemSettings;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class SendOtpNotification extends Notification
{

    protected string $otp;

    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $expirationMinutes = max(1, (int) config('otp.expire_minutes', 5));
        $recipientName = '';
        foreach (['full_name', 'name', 'username'] as $field) {
            $candidate = trim((string) ($notifiable->{$field} ?? ''));

            if ($candidate !== '') {
                $recipientName = $candidate;
                break;
            }
        }
        $systemName = SystemSettings::string('system_name', 'LGU Energy Monitoring System');
        $systemShortName = SystemSettings::string('short_name', 'LGU EMS');
        $emailReference = Str::upper(Str::random(6));

        return (new MailMessage)
            // A unique, non-sensitive reference prevents Gmail from grouping
            // this message with older OTP emails that contained an image part.
            ->subject("Your OTP Code - {$systemShortName} [{$emailReference}]")
            ->view([
                'html' => 'emails.otp',
                'text' => 'emails.otp-text',
            ], [
                'otp' => $this->otp,
                'recipientName' => $recipientName,
                'expirationMinutes' => $expirationMinutes,
                'systemName' => $systemName,
                'systemShortName' => $systemShortName,
            ]);
    }
}
