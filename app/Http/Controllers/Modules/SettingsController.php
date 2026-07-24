<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
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

        $defaults = [
            'system_name' => 'LGU Energy Monitoring System',
            'short_name' => 'LGU EMS',
            'org_name' => 'Local Government Unit',
            'timezone' => 'Asia/Manila',
            'otp_expiration' => '5',
            'max_login_attempts' => '5',
            'session_timeout' => '60',
            'enable_otp_login' => '1',
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
            'allowed_image_types' => 'jpg,png,jpeg',
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

        return view('modules.settings.index', compact(
            'settings',
            'defaults',
            'role',
            'user'
        ));
    }

    public function update(Request $request)
    {
        $this->ensureSettingsAccess();

        $rules = [
            'system_name' => 'required|string|max:255',
            'short_name' => 'required|string|max:100',
            'org_name' => 'required|string|max:255',
            'timezone' => 'required|string|max:100',
            'system_logo' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'favicon' => 'nullable|file|mimes:ico,png,jpg,jpeg,svg|max:1024',
            'otp_expiration' => 'required|integer|min:1|max:60',
            'max_login_attempts' => 'required|integer|min:1|max:15',
            'session_timeout' => 'required|integer|min:1|max:60',
            'enable_otp_login' => 'required|in:0,1',
            'auto_log_incident' => 'required|in:0,1',
            'facility_image_size' => 'required|integer|min:1|max:20',
            'allowed_image_types' => 'required|string|max:120',
            'default_facility_status' => 'required|in:active,inactive',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'enable_email_notifications' => 'required|in:0,1',
            'enable_audit_logs' => 'required|in:0,1',
            'retention_period' => 'required|integer|min:1|max:120',
            'export_format' => 'required|in:pdf,excel',
        ];

        foreach (['small', 'medium', 'large', 'xlarge'] as $size) {
            for ($level = 1; $level <= 5; $level++) {
                $rules["alert_level{$level}_{$size}"] = 'required|numeric|min:0|max:500';
            }
            for ($level = 1; $level <= 3; $level++) {
                $rules["alert_drop_level{$level}_{$size}"] = 'required|numeric|min:0|max:500';
            }
        }
        $validated = $request->validate($rules);

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
                        ->withErrors(["alert_level{$i}_{$size}" => ucfirst($size) . ' thresholds must strictly increase from Level 1 to Level 5.']);
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
                        ->withErrors(["alert_drop_level{$i}_{$size}" => ucfirst($size) . ' drop thresholds must strictly increase from Level 1 to Level 3.']);
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
}
