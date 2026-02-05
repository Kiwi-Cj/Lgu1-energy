<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class OtpController extends Controller
{
    // Resend OTP (AJAX)
    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = \App\Models\User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Rate limit: 1 resend per 30 seconds per email
        $key = 'otp-resend-' . $user->id;
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 1)) {
            return response()->json(['message' => 'Please wait before requesting another OTP.'], 429);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($key, 30);

        $code = rand(100000, 999999);
        $expireMinutes = (int) config('otp.expire_minutes', 5);
        $expiresAt = now()->addMinutes($expireMinutes);
        $otp = \App\Models\Otp::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);
        $user->notify(new \App\Notifications\SendOtpNotification($code));
        return response()->json(['message' => 'OTP resent to your email.']);
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
    // Send OTP to user (for demo, via email)
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = \App\Models\User::where('email', $request->email)->firstOrFail();

        $code = rand(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        $otp = \App\Models\Otp::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);

        // Send OTP notification (implement notification next)
        $user->notify(new \App\Notifications\SendOtpNotification($code));

        return back()->with('success', 'OTP sent to your email.');
    }

    // Verify OTP
    public function verifyOtp(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        \Log::info('OTP VERIFY REQUEST', [
            'input' => $request->all(),
            'cookies' => $request->cookies->all(),
            'session_id' => session()->getId(),
        ]);

        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|digits:6'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('OTP VALIDATION FAILED', ['errors' => $e->errors()]);
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
            }
            throw $e;
        }

        $user = \App\Models\User::where('email', $request->email)->first();
        if (!$user || strtolower($user->status) !== 'active') {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['message' => 'Your account is inactive. Please contact the administrator.'], 401);
            }
            return back()->withErrors(['otp' => 'Your account is inactive. Please contact the administrator.']);
        }
        \Log::info('OTP USER LOOKUP', ['user' => $user ? $user->toArray() : null]);

        if (!$user) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['message' => 'User not found.'], 401);
            }
            return back()->withErrors(['otp' => 'User not found.']);
        }

        $otp = $user->otps()
            ->where('code', $request->otp)
            ->where('expires_at', '>', now())
            ->where('used', false)
            ->latest()
            ->first();
        \Log::info('OTP RECORD LOOKUP', ['otp' => $otp ? $otp->toArray() : null]);

        if ($otp) {
            $otp->used = true;
            $otp->save();
            \Auth::login($user);
            \Log::info('OTP LOGIN', ['user_id' => $user->id, 'auth_check' => auth()->check(), 'session_id' => session()->getId()]);
            session()->forget('show_otp_modal');
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['redirect' => route('dashboard.index')]);
            }
            return redirect()->route('dashboard.index')->with('success', 'OTP verified successfully!');
        }

        // Show modal again on error
        session(['show_otp_modal' => true]);
        \Log::warning('OTP INVALID OR EXPIRED', ['input' => $request->all()]);
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 401);
        }
        return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
    }
    }
