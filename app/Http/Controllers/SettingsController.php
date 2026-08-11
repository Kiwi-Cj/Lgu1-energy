<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Support\RoleAccess;

class SettingsController extends Controller
{
    // Get all relevant settings
    public function index()
    {
        abort_unless(RoleAccess::can(request()->user(), 'access_settings'), 403);

        $keys = [
            'otp_expiration',
            'max_login_attempts',
            'session_timeout',
            'enable_otp_login',
            'system_name',
        ];
        $settings = Setting::getMany($keys);
        return response()->json($settings);
    }

    // Update settings
    public function update(Request $request)
    {
        abort_unless(RoleAccess::can($request->user(), 'access_settings'), 403);

        $data = $request->validate([
            'otp_expiration' => ['sometimes', 'integer', 'min:1', 'max:60'],
            'max_login_attempts' => ['sometimes', 'integer', 'min:1', 'max:15'],
            'session_timeout' => ['sometimes', 'integer', 'min:1', 'max:60'],
            'enable_otp_login' => ['sometimes', 'boolean'],
            'system_name' => ['sometimes', 'string', 'max:255'],
        ]);

        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }
        return response()->json(['success' => true]);
    }
}
