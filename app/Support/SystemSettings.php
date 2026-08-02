<?php

namespace App\Support;

use App\Models\Setting;

final class SystemSettings
{
    public const DEFAULTS = [
        'system_name' => 'LGU Energy Monitoring System',
        'short_name' => 'LGU EMS',
        'org_name' => 'Local Government Unit',
        'timezone' => 'Asia/Manila',
        'otp_expiration' => '5',
        'max_login_attempts' => '5',
        'session_timeout' => '60',
        'enable_otp_login' => '1',
        'baseline_small_max_kwh' => '1000',
        'baseline_medium_max_kwh' => '3000',
        'baseline_large_max_kwh' => '10000',
        'trend_spike_threshold_small' => '10',
        'trend_spike_threshold_medium' => '7',
        'trend_spike_threshold_large' => '4',
        'trend_spike_threshold_xlarge' => '2',
        'alert_level1_small' => '5',
        'alert_level2_small' => '10',
        'alert_level3_small' => '15',
        'alert_level4_small' => '25',
        'alert_level5_small' => '35',
        'alert_level1_medium' => '4',
        'alert_level2_medium' => '8',
        'alert_level3_medium' => '12',
        'alert_level4_medium' => '20',
        'alert_level5_medium' => '30',
        'alert_level1_large' => '3',
        'alert_level2_large' => '6',
        'alert_level3_large' => '10',
        'alert_level4_large' => '16',
        'alert_level5_large' => '24',
        'alert_level1_xlarge' => '2',
        'alert_level2_xlarge' => '4',
        'alert_level3_xlarge' => '7',
        'alert_level4_xlarge' => '12',
        'alert_level5_xlarge' => '18',
        'alert_drop_level1_small' => '5',
        'alert_drop_level2_small' => '10',
        'alert_drop_level3_small' => '15',
        'alert_drop_level1_medium' => '4',
        'alert_drop_level2_medium' => '8',
        'alert_drop_level3_medium' => '12',
        'alert_drop_level1_large' => '3',
        'alert_drop_level2_large' => '6',
        'alert_drop_level3_large' => '10',
        'alert_drop_level1_xlarge' => '2',
        'alert_drop_level2_xlarge' => '4',
        'alert_drop_level3_xlarge' => '7',
        'auto_log_incident' => '1',
        'facility_image_size' => '5',
        'allowed_image_types' => 'jpg,jpeg,png,webp',
        'default_facility_status' => 'active',
        'mail_host' => '',
        'mail_port' => '587',
        'enable_email_notifications' => '1',
        'enable_audit_logs' => '0',
        'retention_period' => '3',
        'export_format' => 'pdf',
        'system_logo' => '',
        'favicon' => '',
    ];

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public static function defaults(): array
    {
        return self::DEFAULTS;
    }

    public static function value(string $key, mixed $fallback = null): mixed
    {
        $default = $fallback ?? (self::DEFAULTS[$key] ?? null);

        try {
            return Setting::getValue($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function string(string $key, ?string $fallback = null): string
    {
        return trim((string) self::value($key, $fallback));
    }

    public static function integer(string $key, ?int $fallback = null): int
    {
        return (int) self::value($key, $fallback);
    }

    public static function enabled(string $key, bool $fallback = false): bool
    {
        $value = self::value($key, $fallback ? '1' : '0');

        return filter_var($value, FILTER_VALIDATE_BOOL);
    }

    public static function emailNotificationsEnabled(): bool
    {
        return self::enabled('enable_email_notifications', true);
    }

    public static function facilityImageExtensions(?string $raw = null): array
    {
        $tokens = preg_split('/[\s,]+/', strtolower($raw ?? self::string('allowed_image_types')))
            ?: [];

        return array_values(array_unique(array_filter(
            array_map(fn (string $extension) => ltrim(trim($extension), '.'), $tokens),
            fn (string $extension) => in_array($extension, self::IMAGE_EXTENSIONS, true)
        )));
    }

    public static function invalidFacilityImageExtensions(string $raw): array
    {
        $tokens = preg_split('/[\s,]+/', strtolower($raw)) ?: [];

        return array_values(array_unique(array_filter(
            array_map(fn (string $extension) => ltrim(trim($extension), '.'), $tokens),
            fn (string $extension) => $extension !== '' && ! in_array($extension, self::IMAGE_EXTENSIONS, true)
        )));
    }

    public static function facilityImageRules(): array
    {
        $extensions = self::facilityImageExtensions();
        if ($extensions === []) {
            $extensions = self::facilityImageExtensions(self::DEFAULTS['allowed_image_types']);
        }

        $maxKilobytes = max(1, min(20, self::integer('facility_image_size', 5))) * 1024;

        return ['nullable', 'image', 'mimes:'.implode(',', $extensions), 'max:'.$maxKilobytes];
    }

    public static function defaultFacilityStatus(): string
    {
        $status = strtolower(self::string('default_facility_status', 'active'));

        return in_array($status, ['active', 'inactive'], true) ? $status : 'active';
    }

    public static function defaultExportFormat(array $allowed = ['pdf', 'xlsx', 'csv']): string
    {
        $format = strtolower(self::string('export_format', 'pdf'));
        $format = $format === 'excel' ? 'xlsx' : $format;

        if (in_array($format, $allowed, true)) {
            return $format;
        }

        return $allowed[0] ?? 'pdf';
    }

    public static function thresholdInputCount(): int
    {
        return count(array_filter(
            array_keys(self::DEFAULTS),
            fn (string $key) => str_starts_with($key, 'alert_level')
                || str_starts_with($key, 'alert_drop_')
                || str_starts_with($key, 'trend_spike_threshold_')
        ));
    }

    public static function brandingFilePath(string $key): ?string
    {
        $storedPath = trim(self::string($key), '/\\');
        if ($storedPath === '' || str_starts_with($storedPath, 'http://') || str_starts_with($storedPath, 'https://')) {
            return null;
        }

        $roots = [public_path()];
        $configuredRoot = trim((string) config('filesystems.public_upload_root', ''));
        if ($configuredRoot !== '') {
            $roots[] = rtrim($configuredRoot, '/\\');
        }
        $cpanelRoot = dirname(base_path()).DIRECTORY_SEPARATOR.'public_html';
        if (is_dir($cpanelRoot)) {
            $roots[] = $cpanelRoot;
        }

        foreach (array_unique($roots) as $root) {
            $candidate = rtrim($root, '/\\').DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $storedPath);
            if (is_file($candidate)) {
                return $candidate;
            }

            $legacyStorageCandidate = rtrim($root, '/\\').DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $storedPath);
            if (is_file($legacyStorageCandidate)) {
                return $legacyStorageCandidate;
            }
        }

        return null;
    }

    public static function brandingUrl(string $key, string $fallback): string
    {
        $storedPath = trim(self::string($key), '/');
        if ($storedPath === '') {
            return asset($fallback);
        }
        if (str_starts_with($storedPath, 'http://') || str_starts_with($storedPath, 'https://')) {
            return $storedPath;
        }
        if (str_starts_with($storedPath, 'uploads/') || str_starts_with($storedPath, 'storage/') || str_starts_with($storedPath, 'img/')) {
            return asset($storedPath);
        }

        return asset('storage/'.$storedPath);
    }
}
