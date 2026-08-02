<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use App\Models\EnergyIncident;
use App\Models\EnergyRecord;
use App\Models\Maintenance;
use App\Observers\EnergyIncidentObserver;
use App\Observers\EnergyRecordObserver;
use App\Observers\MaintenanceObserver;
use App\Models\Setting;
use App\Support\SystemSettings;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
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
        Paginator::useBootstrapFive();

        EnergyRecord::observe(EnergyRecordObserver::class);
        EnergyIncident::observe(EnergyIncidentObserver::class);
        Maintenance::observe(MaintenanceObserver::class);

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
                $runtimeKeys = Setting::getMany([
                    'system_name',
                    'short_name',
                    'org_name',
                    'timezone',
                    'session_timeout',
                    'otp_expiration',
                    'enable_otp_login',
                    'max_login_attempts',
                    'mail_host',
                    'mail_port',
                ]);

                $systemName = trim((string) ($runtimeKeys['system_name'] ?? ''));
                if ($systemName !== '') {
                    config(['app.name' => $systemName]);
                }

                $timezone = trim((string) ($runtimeKeys['timezone'] ?? ''));
                if ($timezone !== '' && in_array($timezone, timezone_identifiers_list(), true)) {
                    config(['app.timezone' => $timezone]);
                    date_default_timezone_set($timezone);
                }

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

                $mailHost = trim((string) ($runtimeKeys['mail_host'] ?? ''));
                $mailPort = (int) ($runtimeKeys['mail_port'] ?? 0);
                if ($mailHost !== '') {
                    config(['mail.mailers.smtp.host' => $mailHost]);
                    if ($mailPort > 0) {
                        config(['mail.mailers.smtp.port' => $mailPort]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Keep default session config when DB/settings table is not yet ready.
        }

        $branding = [
            'system_logo' => null,
            'favicon' => null,
        ];

        try {
            if (Schema::hasTable('settings')) {
                $branding = Setting::getMany(array_keys($branding), $branding);
            }
        } catch (\Throwable $e) {
            // Keep the bundled branding while the database is unavailable.
        }

        $logoPath = trim((string) ($branding['system_logo'] ?? ''), '/');
        $faviconPath = trim((string) ($branding['favicon'] ?? ''), '/');
        $brandingUrl = static function (string $path): ?string {
            if ($path === '') {
                return null;
            }

            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            if (str_starts_with($path, 'uploads/')
                || str_starts_with($path, 'storage/')
                || str_starts_with($path, 'img/')) {
                return asset($path);
            }

            // Backward compatibility for branding previously stored on the
            // Laravel public disk as "settings/<filename>".
            return asset('storage/'.$path);
        };

        View::share([
            'systemLogoUrl' => $brandingUrl($logoPath) ?? asset('img/logocityhall.jpg'),
            'systemFaviconUrl' => $brandingUrl($faviconPath) ?? asset('img/logocityhall.jpg'),
            'systemName' => SystemSettings::string('system_name', 'LGU Energy Monitoring System'),
            'systemShortName' => SystemSettings::string('short_name', 'LGU EMS'),
            'systemOrganization' => SystemSettings::string('org_name', 'Local Government Unit'),
            'defaultFacilityStatus' => SystemSettings::defaultFacilityStatus(),
            'facilityAllowedImageTypes' => SystemSettings::facilityImageExtensions(),
            'facilityImageMaxMb' => max(1, min(20, SystemSettings::integer('facility_image_size', 5))),
            'defaultExportFormat' => SystemSettings::defaultExportFormat(),
        ]);
    }
}
