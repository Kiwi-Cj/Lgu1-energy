<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\RoleAccess;
use App\Support\SystemSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    private function resolveWebPublicRoot(): string
    {
        $configured = trim((string) config('filesystems.public_upload_root', ''));
        if ($configured !== '' && is_dir($configured)) {
            return rtrim($configured, '/\\');
        }

        $cpanelPublicHtml = dirname(base_path()).DIRECTORY_SEPARATOR.'public_html';
        if (is_dir($cpanelPublicHtml)) {
            return rtrim($cpanelPublicHtml, '/\\');
        }

        return public_path();
    }

    private function storeBrandingFileToPublic(Request $request, string $field): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);
        $directory = $this->resolveWebPublicRoot()
            .DIRECTORY_SEPARATOR.'uploads'
            .DIRECTORY_SEPARATOR.'settings';

        try {
            if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                throw new \RuntimeException('Unable to create the branding upload directory.');
            }

            if (! is_writable($directory)) {
                throw new \RuntimeException('The branding upload directory is not writable.');
            }

            $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());
            $filename = $field.'_'.Str::uuid().'.'.$extension;
            $file->move($directory, $filename);
        } catch (\Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                $field => 'Unable to upload this file. Check the public uploads directory permissions.',
            ]);
        }

        return 'uploads/settings/'.$filename;
    }

    /**
     * Only super admin can access settings page.
     */
    protected function ensureSettingsAccess()
    {
        if (! RoleAccess::can(auth()->user(), 'access_settings')) {
            abort(403, 'You do not have permission to access Settings.');
        }
    }

    public function index()
    {
        $this->ensureSettingsAccess();

        $settings = Setting::allAsKeyValue();
        $user = auth()->user();
        $role = RoleAccess::normalize($user);

        $defaults = SystemSettings::defaults();
        $timezones = timezone_identifiers_list();
        $thresholdInputCount = SystemSettings::thresholdInputCount();

        return view('modules.settings.index', compact(
            'settings',
            'defaults',
            'role',
            'user',
            'timezones',
            'thresholdInputCount'
        ));
    }

    public function update(Request $request)
    {
        $this->ensureSettingsAccess();

        $rules = [
            'system_name' => 'required|string|max:255',
            'short_name' => 'required|string|max:100',
            'org_name' => 'required|string|max:255',
            'timezone' => 'required|timezone|max:100',
            'system_logo' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'favicon' => 'nullable|file|mimes:ico,png,jpg,jpeg,svg|max:1024',
            'otp_expiration' => 'required|integer|min:1|max:60',
            'max_login_attempts' => 'required|integer|min:1|max:15',
            'session_timeout' => 'required|integer|min:1|max:60',
            'enable_otp_login' => 'required|in:0,1',
            'baseline_small_max_kwh' => 'required|numeric|min:1|max:100000000',
            'baseline_medium_max_kwh' => 'required|numeric|min:1|max:100000000|gt:baseline_small_max_kwh',
            'baseline_large_max_kwh' => 'required|numeric|min:1|max:100000000|gt:baseline_medium_max_kwh',
            'auto_log_incident' => 'required|in:0,1',
            'facility_image_size' => 'required|integer|min:1|max:20',
            'allowed_image_types' => 'required|string|max:120',
            'default_facility_status' => 'required|in:active,inactive',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'enable_email_notifications' => 'required|in:0,1',
            'enable_audit_logs' => 'required|in:0,1',
            'retention_period' => 'required|integer|min:1|max:120',
            'export_format' => 'required|in:pdf,xlsx,csv,excel',
        ];

        foreach (['small', 'medium', 'large', 'xlarge'] as $size) {
            $rules["trend_spike_threshold_{$size}"] = 'required|numeric|min:0.01|max:100';
            for ($level = 1; $level <= 5; $level++) {
                $rules["alert_level{$level}_{$size}"] = 'required|numeric|min:0|max:500';
            }
            for ($level = 1; $level <= 3; $level++) {
                $rules["alert_drop_level{$level}_{$size}"] = 'required|numeric|min:0|max:500';
            }
        }
        $validated = $request->validate($rules);

        $invalidImageTypes = SystemSettings::invalidFacilityImageExtensions((string) $validated['allowed_image_types']);
        if ($invalidImageTypes !== []) {
            throw ValidationException::withMessages([
                'allowed_image_types' => 'Unsupported image types: '.implode(', ', $invalidImageTypes).'. Allowed: JPG, JPEG, PNG, GIF, WEBP.',
            ]);
        }
        $validated['allowed_image_types'] = implode(',', SystemSettings::facilityImageExtensions((string) $validated['allowed_image_types']));
        $validated['export_format'] = $validated['export_format'] === 'excel' ? 'xlsx' : $validated['export_format'];

        // Ensure alert thresholds are ascending per facility size.
        foreach (['small', 'medium', 'large', 'xlarge'] as $size) {
            $levels = [
                (float) $validated["alert_level1_{$size}"],
                (float) $validated["alert_level2_{$size}"],
                (float) $validated["alert_level3_{$size}"],
                (float) $validated["alert_level4_{$size}"],
                (float) $validated["alert_level5_{$size}"],
            ];

            for ($i = 1; $i < count($levels); $i++) {
                if ($levels[$i] <= $levels[$i - 1]) {
                    return back()
                        ->withInput()
                        ->withErrors(["alert_level".($i + 1)."_{$size}" => ucfirst($size) . ' thresholds must strictly increase from Level 1 to Level 5.']);
                }
            }

            $dropLevels = [
                (float) $validated["alert_drop_level1_{$size}"],
                (float) $validated["alert_drop_level2_{$size}"],
                (float) $validated["alert_drop_level3_{$size}"],
            ];

            for ($i = 1; $i < count($dropLevels); $i++) {
                if ($dropLevels[$i] <= $dropLevels[$i - 1]) {
                    return back()
                        ->withInput()
                        ->withErrors(["alert_drop_level".($i + 1)."_{$size}" => ucfirst($size) . ' drop thresholds must strictly increase from Level 1 to Level 3.']);
                }
            }
        }

        if ($path = $this->storeBrandingFileToPublic($request, 'system_logo')) {
            $validated['system_logo'] = $path;
        } else {
            unset($validated['system_logo']);
        }

        if ($path = $this->storeBrandingFileToPublic($request, 'favicon')) {
            $validated['favicon'] = $path;
        } else {
            unset($validated['favicon']);
        }

        $groupMap = [
            'general' => ['system_name', 'short_name', 'org_name', 'timezone', 'system_logo', 'favicon'],
            'user' => ['otp_expiration', 'max_login_attempts', 'session_timeout', 'enable_otp_login'],
            'energy' => [
                'baseline_small_max_kwh', 'baseline_medium_max_kwh', 'baseline_large_max_kwh',
                'trend_spike_threshold_small', 'trend_spike_threshold_medium', 'trend_spike_threshold_large', 'trend_spike_threshold_xlarge',
                'alert_level1_small', 'alert_level2_small', 'alert_level3_small', 'alert_level4_small', 'alert_level5_small',
                'alert_level1_medium', 'alert_level2_medium', 'alert_level3_medium', 'alert_level4_medium', 'alert_level5_medium',
                'alert_level1_large', 'alert_level2_large', 'alert_level3_large', 'alert_level4_large', 'alert_level5_large',
                'alert_level1_xlarge', 'alert_level2_xlarge', 'alert_level3_xlarge', 'alert_level4_xlarge', 'alert_level5_xlarge',
                'alert_drop_level1_small', 'alert_drop_level2_small', 'alert_drop_level3_small',
                'alert_drop_level1_medium', 'alert_drop_level2_medium', 'alert_drop_level3_medium',
                'alert_drop_level1_large', 'alert_drop_level2_large', 'alert_drop_level3_large',
                'alert_drop_level1_xlarge', 'alert_drop_level2_xlarge', 'alert_drop_level3_xlarge',
                'auto_log_incident',
            ],
            'facility' => ['facility_image_size', 'allowed_image_types', 'default_facility_status'],
            'email' => ['mail_host', 'mail_port', 'enable_email_notifications'],
            'reports' => ['enable_audit_logs', 'retention_period', 'export_format'],
        ];

        $hasGroupColumn = Schema::hasColumn('settings', 'group');
        $keyGroup = [];
        foreach ($groupMap as $group => $keys) {
            foreach ($keys as $key) {
                $keyGroup[$key] = $group;
            }
        }

        foreach ($validated as $key => $value) {
            $setting = Setting::firstOrNew(['key' => $key]);
            $setting->value = (string) $value;
            if ($hasGroupColumn) {
                $setting->setAttribute('group', $keyGroup[$key] ?? null);
            }
            $setting->save();
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }

    public function testEmail(Request $request)
    {
        $this->ensureSettingsAccess();

        $recipient = trim((string) $request->user()?->email);
        if ($recipient === '') {
            return back()->with('error', 'Your account has no email address for the test message.');
        }

        try {
            Mail::raw(
                'This is a test email from '.SystemSettings::string('system_name').'. Your saved mail configuration is working.',
                function ($message) use ($recipient): void {
                    $message->to($recipient)->subject('Mail configuration test');
                }
            );

            return back()->with('success', 'Test email sent to '.$recipient.'.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Test email failed. Verify the saved SMTP host/port and the credentials in your environment configuration.');
        }
    }
}
