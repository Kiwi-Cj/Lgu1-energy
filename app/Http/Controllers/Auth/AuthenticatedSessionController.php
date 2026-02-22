<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->ensureIsNotRateLimited();
        $user = \App\Models\User::where('email', $request->email)->first();
        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password) || strtolower($user->status) !== 'active') {
            \Illuminate\Support\Facades\RateLimiter::hit($request->throttleKey());
            session()->forget(['otp_user_id']);
            $errorMsg = !$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)
                ? trans('auth.failed')
                : 'Your account is inactive. Please contact the administrator.';
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => $errorMsg
                ], 422);
            }
            return back()->withErrors(['email' => $errorMsg]);
        }

        $otpEnabled = (bool) config('otp.enabled', true);
        if (! $otpEnabled) {
            Auth::login($user);
            $request->session()->regenerate();
            \Illuminate\Support\Facades\RateLimiter::clear($request->throttleKey());
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'redirect' => route('dashboard'),
                    'otp_enabled' => false,
                ]);
            }
            return redirect()->route('dashboard');
        }

        session(['otp_user_id' => $user->id]);
        $otp = rand(100000, 999999);
        $expireMinutes = max(1, (int) config('otp.expire_minutes', 5));
        $expiresAt = now()->addMinutes($expireMinutes);
        \App\Models\Otp::create([
            'user_id' => $user->id,
            'code' => $otp,
            'expires_at' => $expiresAt,
        ]);
        \Log::info('Sending OTP to: ' . $user->email);
        $user->notify(new \App\Notifications\SendOtpNotification($otp));
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'show_otp_modal' => true,
                'otp_expire_minutes' => $expireMinutes,
            ]);
        }
        return redirect()->back();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
