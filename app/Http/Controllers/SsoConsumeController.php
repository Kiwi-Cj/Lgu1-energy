<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Accepts a signed token from Main LGU (infragovservices.com SSO hub) and
 * logs the matching (or newly provisioned) user in via the normal 'web'
 * guard, same as AuthenticatedSessionController does for password login.
 */
class SsoConsumeController extends Controller
{
    public function consume(Request $request)
    {
        $token = (string) $request->query('sso_token', '');
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            abort(403, 'SSO error: malformed token');
        }
        [$payloadPart, $signaturePart] = $parts;

        $secret = (string) config('services.sso.secret');
        $expectedSig = rtrim(strtr(base64_encode(hash_hmac('sha256', $payloadPart, $secret, true)), '+/', '-_'), '=');
        if (!hash_equals($expectedSig, $signaturePart)) {
            abort(403, 'SSO error: invalid signature');
        }

        $payload = json_decode(base64_decode(strtr($payloadPart, '-_', '+/')), true);
        if (!is_array($payload)) {
            abort(403, 'SSO error: invalid payload');
        }
        if (($payload['target'] ?? '') !== 'energy') {
            abort(403, 'SSO error: token not issued for this system');
        }
        if (!isset($payload['exp']) || time() > $payload['exp']) {
            abort(403, 'SSO error: token expired');
        }

        try {
            DB::table('sso_used_tokens')->insert(['nonce' => $payload['nonce'] ?? '']);
        } catch (\Illuminate\Database\QueryException $e) {
            abort(403, 'SSO error: token already used');
        }

        $email = (string) ($payload['email'] ?? '');
        $fullName = (string) ($payload['full_name'] ?? 'Super Admin');

        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'full_name' => $fullName,
                'name' => $fullName,
                'email' => $email,
                'username' => 'sso_' . substr(md5($email), 0, 10),
                'password' => Hash::make(Str::random(32)),
                'role' => 'admin',
                'status' => 'active',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('sso_from_mainlgu', true);

        $user->forceFill(['last_login' => now()])->save();

        return redirect()->route('dashboard.index');
    }
}
