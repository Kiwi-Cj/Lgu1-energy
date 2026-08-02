<?php


namespace App\Notifications;

use App\Support\SystemSettings;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        $logoUrl = SystemSettings::brandingUrl('system_logo', 'img/logocityhall.jpg');
        $logoHost = strtolower((string) parse_url($logoUrl, PHP_URL_HOST));
        $logoScheme = strtolower((string) parse_url($logoUrl, PHP_URL_SCHEME));
        $logoIsPublic = in_array($logoScheme, ['http', 'https'], true)
            && $logoHost !== ''
            && ! in_array($logoHost, ['localhost', '127.0.0.1', '::1'], true)
            && ! str_ends_with($logoHost, '.local');

        return (new MailMessage)
            ->subject('Your OTP Code')
            ->view([
                'html' => 'emails.otp',
                'text' => 'emails.otp-text',
            ], [
                'otp' => $this->otp,
                'recipientName' => $recipientName,
                'expirationMinutes' => $expirationMinutes,
                'systemName' => $systemName,
                'systemShortName' => $systemShortName,
                'logoSrc' => $logoIsPublic ? $logoUrl : null,
            ]);
    }
}
