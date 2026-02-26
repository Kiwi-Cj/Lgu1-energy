<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use App\Models\EnergyIncident;
use App\Models\EnergyRecord;
use App\Observers\EnergyIncidentObserver;
use App\Observers\EnergyRecordObserver;
use App\Models\Setting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        EnergyRecord::observe(EnergyRecordObserver::class);
        EnergyIncident::observe(EnergyIncidentObserver::class);

        // Enforce a strong password policy across registration, reset, and profile updates.
        Password::defaults(function () {
            return Password::min(12)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $broker = (string) config('auth.defaults.passwords', 'users');
            $expireMinutes = (int) config("auth.passwords.{$broker}.expire", 60);

            $displayName = trim((string) (
                $notifiable->username
                ?? $notifiable->name
                ?? $notifiable->full_name
                ?? ''
            ));

            $greeting = $displayName !== ''
                ? 'Hello ' . $displayName . '!'
                : 'Hello!';

            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject(Lang::get('Reset Password Notification'))
                ->greeting($greeting)
                ->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
                ->action(Lang::get('Reset Password'), $resetUrl)
                ->line(Lang::get('This password reset link will expire in :count minutes.', ['count' => $expireMinutes]))
                ->line(Lang::get('If you did not request a password reset, no further action is required.'));
        });

        // Sync Laravel session lifetime with dynamic settings value (minutes).
        try {
            if (Schema::hasTable('settings')) {
                $runtimeKeys = Setting::whereIn('key', [
                    'session_timeout',
                    'otp_expiration',
                    'enable_otp_login',
                    'max_login_attempts',
                ])->pluck('value', 'key');

                $minutes = (int) ($runtimeKeys['session_timeout'] ?? 0);
                if ($minutes > 0) {
                    config(['session.lifetime' => $minutes]);
                }

                $otpExpiration = (int) ($runtimeKeys['otp_expiration'] ?? 0);
                if ($otpExpiration > 0) {
                    config(['otp.expire_minutes' => $otpExpiration]);
                }

                $otpEnabledRaw = $runtimeKeys['enable_otp_login'] ?? null;
                if ($otpEnabledRaw !== null) {
                    config(['otp.enabled' => ((int) $otpEnabledRaw) === 1]);
                }

                $maxLoginAttempts = (int) ($runtimeKeys['max_login_attempts'] ?? 0);
                if ($maxLoginAttempts > 0) {
                    config(['otp.max_login_attempts' => $maxLoginAttempts]);
                }
            }
        } catch (\Throwable $e) {
            // Keep default session config when DB/settings table is not yet ready.
        }
    }
}
