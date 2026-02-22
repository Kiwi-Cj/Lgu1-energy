<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class OtpController extends Controller
{
    protected function otpExpireMinutes(): int
    {
        return max(1, (int) config('otp.expire_minutes', 5));
    }

    protected function otpEnabled(): bool
    {
        return (bool) config('otp.enabled', true);
    }

    // Resend OTP (AJAX)
    public function resendOtp(Request $request): JsonResponse
    {
        if (! $this->otpEnabled()) {
            return response()->json(['message' => 'OTP login is disabled by system settings.'], 403);
        }

        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Rate limit: 1 resend per 30 seconds per user
        $key = 'otp-resend-' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            return response()->json(['message' => 'Please wait before requesting another OTP.'], 429);
        }
        RateLimiter::hit($key, 30);

        $code = random_int(100000, 999999);
        $expiresAt = now()->addMinutes($this->otpExpireMinutes());
        Otp::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);
        $user->notify(new \App\Notifications\SendOtpNotification((string) $code));

        return response()->json([
            'message' => 'OTP resent to your email.',
            'otp_expire_minutes' => $this->otpExpireMinutes(),
        ]);
    }

    // Show OTP request form
    public function showRequestForm()
    {
        return view('auth.otp-request');
    }

    // Show OTP verify form
    public function showVerifyForm()
    {
        return view('auth.otp-verify');
    }

    // Send OTP to user
    public function sendOtp(Request $request): RedirectResponse|JsonResponse
    {
        if (! $this->otpEnabled()) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['message' => 'OTP login is disabled by system settings.'], 403);
            }
            return back()->withErrors(['email' => 'OTP login is disabled by system settings.']);
        }

        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->firstOrFail();

        $code = random_int(100000, 999999);
        $expiresAt = now()->addMinutes($this->otpExpireMinutes());

        Otp::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);

        $user->notify(new \App\Notifications\SendOtpNotification((string) $code));

        return back()->with('success', 'OTP sent to your email.');
    }

    // Verify OTP
    public function verifyOtp(Request $request): RedirectResponse|JsonResponse
    {
        if (! $this->otpEnabled()) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['message' => 'OTP login is disabled by system settings.'], 403);
            }
            return back()->withErrors(['otp' => 'OTP login is disabled by system settings.']);
        }

        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user || strtolower((string) $user->status) !== 'active') {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['message' => 'Your account is inactive. Please contact the administrator.'], 401);
            }
            return back()->withErrors(['otp' => 'Your account is inactive. Please contact the administrator.']);
        }

        $otp = $user->otps()
            ->where('code', $request->otp)
            ->where('expires_at', '>', now())
            ->where('used', false)
            ->latest()
            ->first();

        if ($otp) {
            $otp->used = true;
            $otp->save();

            Auth::login($user);
            session()->forget('show_otp_modal');

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['redirect' => route('dashboard.index')]);
            }
            return redirect()->route('dashboard.index')->with('success', 'OTP verified successfully!');
        }

        session(['show_otp_modal' => true]);
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Invalid or expired OTP.',
                'otp_expire_minutes' => $this->otpExpireMinutes(),
            ], 401);
        }
        return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
    }
}
