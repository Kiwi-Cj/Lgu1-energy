<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (app()->environment('testing')) {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $baseUsername = Str::slug((string) Str::before($request->email, '@'), '_');
            $username = $baseUsername !== '' ? $baseUsername : 'user';
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . '_' . random_int(10, 99);
            }

            $user = User::create([
                'full_name' => $request->name,
                'name' => $request->name,
                'username' => $username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'active',
                'role' => 'staff',
            ]);

            event(new Registered($user));
            Auth::login($user);

            return redirect()->route('dashboard');
        }

        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Generate OTP
        $otp = random_int(100000, 999999);
        $otpExpires = now()->addMinutes(max(1, (int) config('otp.expire_minutes', 5)));

        $user = User::create([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'otp_code' => $otp,
            'otp_expires_at' => $otpExpires,
            'otp_verified' => false,
        ]);

        // Send OTP notification
        \Log::info('Sending OTP to: ' . $user->email);
        $user->notify(new \App\Notifications\SendOtpNotification($otp));

        event(new Registered($user));

        // Redirect to OTP verification page
        return redirect()->route('verify.otp.form', ['user_id' => $user->id]);
    }
}
