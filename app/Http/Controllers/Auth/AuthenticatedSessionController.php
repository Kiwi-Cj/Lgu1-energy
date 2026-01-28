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
        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            \Illuminate\Support\Facades\RateLimiter::hit($request->throttleKey());
            session()->forget(['otp_user_id']);
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => trans('auth.failed')
                ], 422);
            }
            return back()->withErrors(['email' => trans('auth.failed')]);
        }
        session(['otp_user_id' => $user->id]);
        $otp = rand(100000, 999999);
        $expireMinutes = (int) env('OTP_EXPIRE_MINUTES', 5);
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
                'show_otp_modal' => true
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
