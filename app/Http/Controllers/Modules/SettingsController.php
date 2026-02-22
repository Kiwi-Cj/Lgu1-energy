<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class SettingsController extends Controller
{
    /**
     * Only admin/super admin can access settings page.
     */
    protected function ensureSettingsAccess()
    {
        $role = strtolower((string) (auth()->user()->role ?? ''));
        if (!in_array($role, ['super admin', 'admin'], true)) {
            abort(403, 'You do not have permission to access Settings.');
        }
    }

    public function index()
    {
        $this->ensureSettingsAccess();

        $settings = Setting::query()->pluck('value', 'key')->toArray();
        $user = auth()->user();
        $role = strtolower((string) ($user->role ?? ''));
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;

        $defaults = [
            'system_name' => 'LGU Energy Monitoring System',
            'short_name' => 'LGU EMS',
            'org_name' => 'Local Government Unit',
            'timezone' => 'Asia/Manila',
            'otp_expiration' => '5',
            'max_login_attempts' => '5',
            'session_timeout' => '120',
            'enable_otp_login' => '1',
            'alert_level1_small' => '3',
            'alert_level2_small' => '5',
            'alert_level3_small' => '10',
            'alert_level4_small' => '20',
            'alert_level5_small' => '30',
            'alert_level1_medium' => '5',
            'alert_level2_medium' => '7',
            'alert_level3_medium' => '13',
            'alert_level4_medium' => '23',
            'alert_level5_medium' => '35',
            'alert_level1_large' => '7',
            'alert_level2_large' => '10',
            'alert_level3_large' => '16',
            'alert_level4_large' => '26',
            'alert_level5_large' => '40',
            'alert_level1_xlarge' => '10',
            'alert_level2_xlarge' => '12',
            'alert_level3_xlarge' => '18',
            'alert_level4_xlarge' => '28',
            'alert_level5_xlarge' => '45',
            'auto_log_incident' => '1',
            'facility_image_size' => '5',
            'allowed_image_types' => 'jpg,png,jpeg',
            'default_facility_status' => 'active',
            'mail_host' => '',
            'mail_port' => '587',
            'enable_email_notifications' => '1',
            'enable_audit_logs' => '1',
            'retention_period' => '12',
            'export_format' => 'pdf',
            'system_logo' => '',
            'favicon' => '',
        ];

        return view('modules.settings.index', compact(
            'settings',
            'defaults',
            'role',
            'user',
            'notifications',
            'unreadNotifCount'
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
            'session_timeout' => 'required|integer|min:5|max:720',
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
        }

        if ($request->hasFile('system_logo')) {
            $validated['system_logo'] = $request->file('system_logo')->store('settings', 'public');
        } else {
            unset($validated['system_logo']);
        }

        if ($request->hasFile('favicon')) {
            $validated['favicon'] = $request->file('favicon')->store('settings', 'public');
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
