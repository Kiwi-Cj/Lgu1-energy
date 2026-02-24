<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        $action = (string) $request->input('submit_action', 'send_otp');

        $rules = [
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
        ];

        if ($action === 'verify_otp') {
            $rules['otp'] = ['required', 'digits:6'];
        }

        $validated = $request->validate($rules);

        $accountMatches = User::query()
            ->where('email', $validated['email'])
            ->where('username', $validated['username'])
            ->first();

        if (! $accountMatches) {
            return back()
                ->withInput($request->only('username', 'email'))
                ->withErrors([
                    'username' => 'The username and email do not match an existing account.',
                ]);
        }

        if ($action !== 'verify_otp') {
            $rateLimitKey = 'forgot-password-otp-' . $accountMatches->id;
            if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
                return back()
                    ->withInput($request->only('username', 'email'))
                    ->withErrors([
                        'otp' => 'Please wait 30 seconds before requesting another OTP.',
                    ]);
            }
            RateLimiter::hit($rateLimitKey, 30);

            $code = (string) random_int(100000, 999999);
            Otp::create([
                'user_id' => $accountMatches->id,
                'code' => $code,
                'expires_at' => now()->addMinutes(max(1, (int) config('otp.expire_minutes', 5))),
                'used' => false,
            ]);

            $accountMatches->notify(new \App\Notifications\SendOtpNotification($code));

            $request->session()->put('password_reset_otp_pending', [
                'user_id' => $accountMatches->id,
                'username' => $validated['username'],
                'email' => $validated['email'],
            ]);

            return back()
                ->withInput($request->only('username', 'email'))
                ->with('otp_status', 'OTP sent successfully to your email. Check your inbox/spam, then enter the 6-digit code below.');
        }

        $pending = $request->session()->get('password_reset_otp_pending');
        $pendingMatches = is_array($pending)
            && (($pending['user_id'] ?? null) === $accountMatches->id)
            && (($pending['username'] ?? null) === $validated['username'])
            && (($pending['email'] ?? null) === $validated['email']);

        if (! $pendingMatches) {
            return back()
                ->withInput($request->only('username', 'email'))
                ->withErrors([
                    'otp' => 'Please request a new OTP first.',
                ]);
        }

        $otp = $accountMatches->otps()
            ->where('code', $validated['otp'])
            ->where('expires_at', '>', now())
            ->where('used', false)
            ->latest()
            ->first();

        if (! $otp) {
            return back()
                ->withInput($request->only('username', 'email'))
                ->withErrors([
                    'otp' => 'Invalid or expired OTP.',
                ]);
        }

        $otp->forceFill(['used' => true])->save();
        $request->session()->forget('password_reset_otp_pending');

        $status = Password::sendResetLink(['email' => $validated['email']]);

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', 'Password reset link sent successfully to your email. Please check your inbox/spam folder.')
            : back()->withInput($request->only('username', 'email'))
                ->withErrors(['email' => 'Unable to send the password reset link right now. Please try again.']);
    }
}
