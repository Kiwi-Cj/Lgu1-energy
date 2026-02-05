<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    // Get all relevant settings
    public function index()
    {
        $keys = [
            'otp_expiration',
            'otp_max_attempts',
            'session_timeout',
            'otp_login',
            'system_name',
        ];
        $settings = Setting::whereIn('key', $keys)->pluck('value', 'key');
        return response()->json($settings);
    }

    // Update settings
    public function update(Request $request)
    {
        $data = $request->only([
            'otp_expiration',
            'otp_max_attempts',
            'session_timeout',
            'otp_login',
            'system_name',
        ]);
        // Validate: all values must be non-null strings
        foreach ($data as $key => $value) {
            if ($value === null) {
                return response()->json(['error' => $key . ' cannot be null'], 422);
            }
            Setting::setValue($key, $value);
        }
        return response()->json(['success' => true]);
    }
}
