<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class OtpVerificationController extends Controller
{
    public function show(Request $request)
    {
        $user_id = $request->session()->get('otp_user_id') ?? $request->query('user_id');
        return view('auth.verify-otp', ['user_id' => $user_id]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp_code' => 'required|string|max:10',
        ]);
        $user = User::find($request->user_id);
        if (!$user || !$user->otp_code || $user->otp_code !== $request->otp_code || !$user->otp_expires_at || now()->gt($user->otp_expires_at)) {
            return redirect()->back()->with('error', 'Invalid or expired OTP.');
        }
        $user->otp_verified = true;
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();
        Auth::login($user);
        $request->session()->forget('otp_user_id');
        return redirect('/modules/dashboard/index');
    }
}
