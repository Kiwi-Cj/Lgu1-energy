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
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();
        $user = \App\Models\User::where('email', $request->email)->first();
        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            \Illuminate\Support\Facades\RateLimiter::hit($request->throttleKey());
            return back()->withErrors(['email' => trans('auth.failed')]);
        }
        \Illuminate\Support\Facades\Auth::login($user);
        $request->session()->regenerate();
        return redirect()->intended('/modules/dashboard/index');
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
