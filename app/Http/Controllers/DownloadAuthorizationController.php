<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DownloadAuthorizationController extends Controller
{
    private const MAX_ATTEMPTS = 3;
    private const PENALTY_SECONDS = 60;

    public function authorize(Request $request)
    {
        $validated = $request->validate([
            'download_password' => ['required', 'string'],
            'target' => ['required', 'string'],
        ]);

        $user = $request->user();
        $attemptKey = 'download_password_attempts.' . ($user?->id ?? 'guest');
        $lockKey = 'download_password_lock_until.' . ($user?->id ?? 'guest');
        $lockedUntil = (int) $request->session()->get($lockKey, 0);

        if ($lockedUntil > now()->timestamp) {
            $retryAfter = $lockedUntil - now()->timestamp;

            return $this->failureResponse(
                $request,
                "Too many invalid attempts. Try again in {$retryAfter} seconds.",
                429,
                ['retry_after' => $retryAfter]
            );
        }

        if (! $user || ! Hash::check($validated['download_password'], (string) $user->password)) {
            $attempts = ((int) $request->session()->get($attemptKey, 0)) + 1;
            $remaining = max(0, self::MAX_ATTEMPTS - $attempts);
            $request->session()->put($attemptKey, $attempts);

            if ($attempts >= self::MAX_ATTEMPTS) {
                $lockedUntil = now()->addSeconds(self::PENALTY_SECONDS)->timestamp;
                $request->session()->put($lockKey, $lockedUntil);
                $request->session()->forget($attemptKey);

                return $this->failureResponse(
                    $request,
                    'Invalid password. Too many attempts. Please wait 60 seconds before trying again.',
                    429,
                    ['retry_after' => self::PENALTY_SECONDS, 'remaining_attempts' => 0]
                );
            }

            return $this->failureResponse(
                $request,
                "Invalid password. {$remaining} " . ($remaining === 1 ? 'attempt' : 'attempts') . ' remaining.',
                422,
                ['remaining_attempts' => $remaining]
            );
        }

        $target = $this->safeTargetUrl($validated['target']);
        if ($target === null) {
            return $this->failureResponse($request, 'Invalid download request.', 422);
        }

        $request->session()->forget($attemptKey);
        $request->session()->forget($lockKey);

        $token = Str::random(40);
        $request->session()->put('download_authorizations.' . $token, [
            'target' => $this->normalizeTarget($target),
            'expires_at' => now()->addMinutes(2)->timestamp,
        ]);

        $redirectUrl = $this->appendToken($target, $token);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password confirmed. Download starting...',
                'redirect_url' => $redirectUrl,
            ]);
        }

        return redirect()->to($redirectUrl);
    }

    private function failureResponse(Request $request, string $message, int $status = 422, array $extra = [])
    {
        if ($request->expectsJson()) {
            return response()->json(array_merge([
                'success' => false,
                'message' => $message,
            ], $extra), $status);
        }

        return back()->with('error', $message);
    }

    private function safeTargetUrl(string $target): ?string
    {
        $target = trim($target);
        if ($target === '') {
            return null;
        }

        $appUrl = rtrim(url('/'), '/');
        if (str_starts_with($target, $appUrl . '/')) {
            return $target;
        }

        if (str_starts_with($target, '/') && ! str_starts_with($target, '//')) {
            return url($target);
        }

        return null;
    }

    private function appendToken(string $target, string $token): string
    {
        $separator = str_contains($target, '?') ? '&' : '?';

        return $target . $separator . 'download_token=' . urlencode($token);
    }

    private function normalizeTarget(string $target): string
    {
        $parts = parse_url($target);
        $path = $parts['path'] ?? '/';
        $query = [];

        if (! empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }
        unset($query['download_token']);
        ksort($query);

        return $path . ($query ? '?' . http_build_query($query) : '');
    }
}
