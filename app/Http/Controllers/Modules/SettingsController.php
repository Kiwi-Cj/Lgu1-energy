<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
        return view('modules.settings.index', compact('settings', 'role', 'user', 'notifications', 'unreadNotifCount'));
    }

    public function update(Request $request)
    {
        foreach ($request->except(['_token', '_method']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], [
                'value' => $value,
                'group' => $request->input('group_' . $key, null)
            ]);
        }
        return redirect()->back()->with('success', 'Settings updated successfully!');
    }
}
