<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\EnergyRecord;
use App\Observers\EnergyRecordObserver;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
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

        // Enforce a strong password policy across registration, reset, and profile updates.
        Password::defaults(function () {
            return Password::min(12)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();
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
