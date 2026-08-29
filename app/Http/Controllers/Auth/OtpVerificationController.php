<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Notifications\SendOtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OtpVerificationController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Please sign in first to request an OTP.']);
        }

        return view('auth.verify-otp', [
            'maskedEmail' => $this->maskEmail($user->email),
            'expiresAt' => (int) $request->session()->get('otp_expires_at', now()->timestamp),
            'resendAvailableAt' => (int) $request->session()->get('otp_resend_available_at', now()->timestamp),
        ]);
    }

    public function verify(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'otp_code' => ['required', 'digits:6'],
        ]);

        $user = $this->pendingUser($request);
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your OTP session has ended. Please sign in again.',
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Your OTP session has ended. Please sign in again.']);
        }

        $otp = Otp::where('user_id', $user->id)
            ->where('code', $request->otp_code)
            ->where('expires_at', '>', now())
            ->where('used', false)
            ->latest()
            ->first();

        if (! $otp) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Invalid or expired OTP.',
                ], 422);
            }

            return back()
                ->withErrors(['otp_code' => 'Invalid or expired OTP.'])
                ->withInput();
        }

        $otp->update(['used' => true]);
        Auth::login($user);
        $user->forceFill(['last_login' => now()])->save();
        $request->session()->forget([
            'otp_user_id',
            'otp_email',
            'otp_expires_at',
            'otp_resend_available_at',
        ]);
        $request->session()->regenerate();

        RateLimiter::clear(
            Str::transliterate(Str::lower($user->email).'|'.$request->ip())
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'OTP verified successfully.',
                'redirect' => route('dashboard'),
            ]);
        }

        return redirect()->route('dashboard')
            ->with('success', 'OTP verified successfully.');
    }

    public function resend(Request $request): JsonResponse|RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Your OTP session has ended. Please sign in again.'], 401)
                : redirect()->route('login');
        }

        $key = 'otp-resend-'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = max(1, RateLimiter::availableIn($key));
            $resendAvailableAt = now()->addSeconds($seconds)->timestamp;
            $request->session()->put('otp_resend_available_at', $resendAvailableAt);

            return response()->json([
                'message' => "Please wait {$seconds} seconds before requesting another OTP.",
                'retry_after' => $seconds,
                'resend_available_at' => $resendAvailableAt,
            ], 429);
        }

        $resendCooldown = max(1, (int) config('otp.resend_cooldown_seconds', 30));
        RateLimiter::hit($key, $resendCooldown);
        $user->otps()->where('used', false)->update(['used' => true]);

        $code = random_int(100000, 999999);
        $expiresAt = now()->addMinutes(max(1, (int) config('otp.expire_minutes', 5)));

        Otp::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);

        $resendAvailableAt = now()->addSeconds($resendCooldown);
        $request->session()->put([
            'otp_expires_at' => $expiresAt->timestamp,
            'otp_resend_available_at' => $resendAvailableAt->timestamp,
        ]);
        $user->notify(new SendOtpNotification((string) $code));

        return response()->json([
            'message' => 'A new OTP was sent to your email.',
            'expires_at' => $expiresAt->timestamp,
            'resend_available_at' => $resendAvailableAt->timestamp,
            'retry_after' => $resendCooldown,
        ]);
    }

    private function pendingUser(Request $request): ?User
    {
        if (! config('otp.enabled', true)) {
            return null;
        }

        $userId = $request->session()->get('otp_user_id');
        if (! $userId) {
            return null;
        }

        return User::query()
            ->whereKey($userId)
            ->whereRaw('LOWER(status) = ?', ['active'])
            ->first();
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visibleLength = max(1, min(2, mb_strlen($local)));
        $visible = mb_substr($local, 0, $visibleLength);

        return $visible.'••••@'.$domain;
    }
}
