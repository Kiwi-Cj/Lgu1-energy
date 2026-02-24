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
        $otp = \App\Models\Otp::where('user_id', $user->id)
            ->where('code', $request->otp_code)
            ->where('expires_at', '>', now())
            ->where('used', false)
            ->latest()
            ->first();
        if (!$otp) {
            return redirect()->back()->with('error', 'Invalid or expired OTP.');
        }
        $otp->used = true;
        $otp->save();
        Auth::login($user);
        $user->forceFill(['last_login' => now()])->save();
        $request->session()->forget('otp_user_id');
        return redirect('/modules/dashboard/index');
    }

    public function verifyOtp(Request $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();

        if ($request->otp == $user->otp_code && now()->lessThan($user->otp_expires_at)) {
            // OTP valid
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();

            return response()->json(['message' => 'OTP verified successfully']);
        } else {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }
    }
}
