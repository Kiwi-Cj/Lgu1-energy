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

        $target = $this->safeTargetUrl($request, $validated['target']);
        if ($target === null) {
            return $this->failureResponse($request, 'Invalid download request.', 422);
        }

        $request->session()->forget($attemptKey);
        $request->session()->forget($lockKey);

        if ($target === 'print') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'action' => 'print',
                    'message' => 'Password confirmed. Opening print dialog...',
                    'redirect_url' => 'print',
                ]);
            }

            return back()->with('success', 'Password confirmed.');
        }

        $token = Str::random(40);
        $request->session()->put('download_authorizations.' . $token, [
            'target' => $this->normalizeTarget($target, $request),
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

    private function safeTargetUrl(Request $request, string $target): ?string
    {
        $target = trim($target);
        if ($target === '') {
            return null;
        }

        if (in_array($target, ['print', '#print', 'action:print'], true)) {
            return 'print';
        }

        $parsed = parse_url($target);
        if ($parsed === false) {
            return null;
        }

        // Relative path starting with '/' but not '//'
        if (! isset($parsed['host'])) {
            if (str_starts_with($target, '//')) {
                return null;
            }

            $relative = '/' . ltrim($target, '/');
            $root = rtrim($request->root(), '/');
            $basePath = (string) $request->getBasePath();
            if ($basePath !== '' && ! str_starts_with($relative, $basePath)) {
                $relative = $basePath . $relative;
            }

            return $root . $relative;
        }

        $allowedHosts = array_filter([
            $request->getHost(),
            parse_url(config('app.url'), PHP_URL_HOST),
            'localhost',
            '127.0.0.1',
        ]);

        $targetHost = strtolower($parsed['host'] ?? '');
        $isAllowed = in_array($targetHost, array_map('strtolower', $allowedHosts), true);

        if (! $isAllowed) {
            return null;
        }

        $scheme = $request->getScheme();
        $httpHost = $request->getHttpHost();
        $path = $parsed['path'] ?? '/';
        $query = ! empty($parsed['query']) ? '?' . $parsed['query'] : '';

        return "{$scheme}://{$httpHost}{$path}{$query}";
    }

    private function appendToken(string $target, string $token): string
    {
        $separator = str_contains($target, '?') ? '&' : '?';

        return $target . $separator . 'download_token=' . urlencode($token);
    }

    private function normalizeTarget(string $target, ?Request $request = null): string
    {
        $parts = parse_url($target);
        $rawPath = $parts['path'] ?? '/';

        if ($request) {
            $basePath = (string) $request->getBasePath();
            if ($basePath !== '' && str_starts_with($rawPath, $basePath)) {
                $rawPath = substr($rawPath, strlen($basePath));
            }
        }

        $path = '/' . ltrim($rawPath, '/');
        $query = [];

        if (! empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }
        unset($query['download_token']);
        ksort($query);

        return $path . ($query ? '?' . http_build_query($query) : '');
    }
}
