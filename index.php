<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$renderWelcomeFallback = static function (): void {
    $welcomePath = __DIR__.'/resources/views/welcome.blade.php';

    if (! file_exists($welcomePath)) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Welcome page not found.';
        exit;
    }

    $content = file_get_contents($welcomePath);
    if ($content === false) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Unable to read welcome page.';
        exit;
    }

    // Basic Blade-like replacements so the public landing page can render
    // even when Laravel cannot boot (e.g., missing vendor on shared hosting).
    $content = preg_replace_callback(
        "/\{\{\s*asset\(['\"]([^'\"]+)['\"]\)\s*\}\}/",
        static function (array $matches): string {
            $path = ltrim($matches[1], '/');

            if (file_exists(__DIR__.'/public/'.$path)) {
                return '/public/'.$path;
            }

            return '/'.$path;
        },
        $content
    );

    $content = preg_replace_callback(
        "/\{\{\s*url\(['\"]([^'\"]*)['\"]\)\s*\}\}/",
        static function (array $matches): string {
            $path = $matches[1];
            if ($path === '' || $path === '/') {
                return '/';
            }

            return '/'.ltrim($path, '/');
        },
        $content
    );

    $content = preg_replace_callback(
        "/\{\{\s*route\(['\"]([^'\"]+)['\"]\)\s*\}\}/",
        static function (array $matches): string {
            $routeName = $matches[1];

            if ($routeName === 'login') {
                return '/login';
            }

            if ($routeName === 'dashboard' || $routeName === 'dashboard.index') {
                return '/dashboard';
            }

            return '#';
        },
        $content
    );

    // Render guest block only (fallback has no auth/session awareness).
    $content = preg_replace('/@guest\s*/', '', $content);
    $content = preg_replace('/@else[\s\S]*?@endguest/', '', $content);
    $content = str_replace('@endguest', '', $content);

    // Remove remaining Blade echoes/directives to avoid raw tags in output.
    $content = preg_replace('/\{\{[\s\S]*?\}\}/', '', $content);
    $content = preg_replace('/^\s*@\w+.*$/m', '', $content);

    header('Content-Type: text/html; charset=utf-8');
    echo $content;
    exit;
};

$envCandidates = [
    getenv('LARAVEL_BASE_PATH') ?: null,
    $_SERVER['LARAVEL_BASE_PATH'] ?? null,
    $_SERVER['REDIRECT_LARAVEL_BASE_PATH'] ?? null,
    $_ENV['LARAVEL_BASE_PATH'] ?? null,
];

$explicitBasePath = null;
foreach ($envCandidates as $envPath) {
    if (! is_string($envPath)) {
        continue;
    }

    $envPath = trim($envPath);
    if ($envPath !== '') {
        $explicitBasePath = rtrim($envPath, '/\\');
        break;
    }
}

$publicPath = __DIR__;
$parentPath = dirname(__DIR__);
$checkedPaths = [];
$candidatePaths = [];
$laravelLikePaths = [];
$openBaseDirRaw = (string) ini_get('open_basedir');
$openBaseDirs = [];

if ($openBaseDirRaw !== '') {
    foreach (explode(PATH_SEPARATOR, $openBaseDirRaw) as $allowedPath) {
        $allowedPath = trim($allowedPath);
        if ($allowedPath === '') {
            continue;
        }

        $openBaseDirs[] = rtrim(str_replace('\\', '/', $allowedPath), '/');
    }
}

$normalizePath = static function (string $path): string {
    return rtrim(str_replace('\\', '/', $path), '/');
};

$startsWith = static function (string $haystack, string $needle): bool {
    if ($needle === '') {
        return true;
    }

    return substr($haystack, 0, strlen($needle)) === $needle;
};

$contains = static function (string $haystack, string $needle): bool {
    if ($needle === '') {
        return true;
    }

    return strpos($haystack, $needle) !== false;
};

$isPathAllowed = static function (?string $path) use ($openBaseDirs, $normalizePath, $startsWith): bool {
    if (! is_string($path) || trim($path) === '') {
        return false;
    }

    if ($openBaseDirs === []) {
        return true;
    }

    $normalizedPath = $normalizePath(trim($path));

    foreach ($openBaseDirs as $allowedPath) {
        if (
            $normalizedPath === $allowedPath ||
            $startsWith($normalizedPath.'/', $allowedPath.'/')
        ) {
            return true;
        }
    }

    return false;
};

$safeIsDir = static function (?string $path) use ($isPathAllowed): bool {
    return $isPathAllowed($path) && is_dir($path);
};

$safeFileExists = static function (?string $path) use ($isPathAllowed): bool {
    return $isPathAllowed($path) && file_exists($path);
};

$safeGlobDirs = static function (?string $pathPattern) use ($isPathAllowed): array {
    if (! is_string($pathPattern) || $pathPattern === '') {
        return [];
    }

    $baseDir = dirname($pathPattern);
    if (! $isPathAllowed($baseDir)) {
        return [];
    }

    return glob($pathPattern, GLOB_ONLYDIR) ?: [];
};

$pushCandidate = static function (?string $path) use (&$candidatePaths, $isPathAllowed): void {
    if (! is_string($path)) {
        return;
    }

    $path = trim($path);
    if ($path === '') {
        return;
    }

    if (! $isPathAllowed($path)) {
        return;
    }

    $candidatePaths[] = rtrim($path, '/\\');
};

$pushCandidate($explicitBasePath);
$pushCandidate($publicPath);
$pushCandidate($parentPath);
$pushCandidate(dirname($parentPath));
$pushCandidate($_SERVER['DOCUMENT_ROOT'] ?? null);
$pushCandidate(isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : null);
$pushCandidate($publicPath.'/laravel');
$pushCandidate($publicPath.'/laravel_app');
$pushCandidate($publicPath.'/app');
$pushCandidate($parentPath.'/laravel');
$pushCandidate($parentPath.'/laravel_app');
$pushCandidate($parentPath.'/app');

if ($safeIsDir($parentPath)) {
    foreach ($safeGlobDirs($parentPath.'/*') as $dir) {
        $candidatePaths[] = $dir;
    }
}

if ($safeIsDir($publicPath)) {
    foreach ($safeGlobDirs($publicPath.'/*') as $dir) {
        $candidatePaths[] = $dir;
    }
}

$levelOneDirs = array_values(array_unique(array_filter($candidatePaths, $safeIsDir)));
foreach ($levelOneDirs as $dir) {
    foreach ($safeGlobDirs($dir.'/*') as $subdir) {
        $candidatePaths[] = $subdir;
    }
}

foreach (array_values(array_unique($candidatePaths)) as $dir) {
    if (! $safeIsDir($dir)) {
        continue;
    }

    if ($safeIsDir($dir.'/current')) {
        $candidatePaths[] = $dir.'/current';
    }

    if ($safeIsDir($dir.'/releases')) {
        foreach ($safeGlobDirs($dir.'/releases/*') as $releaseDir) {
            $candidatePaths[] = $releaseDir;
        }
    }

    if (basename($dir) === 'public') {
        $candidatePaths[] = dirname($dir);
    }
}

$basePath = null;
$autoloadFile = null;
foreach (array_values(array_unique($candidatePaths)) as $candidate) {
    $checkedPaths[] = $candidate;

    $autoloadPath = $candidate.'/vendor/autoload.php';
    $nestedAutoloadPath = $candidate.'/vendor/vendor/autoload.php';
    $bootstrapPath = $candidate.'/bootstrap/app.php';
    $resolvedAutoloadPath = null;

    if ($safeFileExists($autoloadPath)) {
        $resolvedAutoloadPath = $autoloadPath;
    } elseif ($safeFileExists($nestedAutoloadPath)) {
        $resolvedAutoloadPath = $nestedAutoloadPath;
    }

    if ($resolvedAutoloadPath !== null && $safeFileExists($bootstrapPath)) {
        $basePath = $candidate;
        $autoloadFile = $resolvedAutoloadPath;
        break;
    }

    if (
        $safeFileExists($bootstrapPath) ||
        $safeIsDir($candidate.'/bootstrap') ||
        $safeIsDir($candidate.'/vendor') ||
        $safeFileExists($candidate.'/public/index.php')
    ) {
        $laravelLikePaths[] = sprintf(
            '%s (bootstrap/app.php: %s, vendor/autoload.php: %s%s)',
            $candidate,
            $safeFileExists($bootstrapPath) ? 'found' : 'missing',
            $safeFileExists($autoloadPath) ? 'found' : 'missing',
            $safeFileExists($nestedAutoloadPath) ? ', nested vendor/vendor/autoload.php: found' : ''
        );
    }
}

if ($basePath === null) {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($requestPath === '/' || $requestPath === '/index.php') {
        $renderWelcomeFallback();
    }

    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Laravel bootstrap files not found.\n";
    echo "Set LARAVEL_BASE_PATH to your Laravel app directory.\n";

    if ($explicitBasePath !== null) {
        echo "LARAVEL_BASE_PATH value: {$explicitBasePath}\n";
    }

    if ($laravelLikePaths !== []) {
        echo "Laravel-like paths found (missing file details):\n - ".implode("\n - ", array_unique($laravelLikePaths));
    } else {
        echo "Checked paths:\n - ".implode("\n - ", $checkedPaths);
    }

    exit;
}

if ($safeFileExists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$autoloadFile ??= $basePath.'/vendor/autoload.php';
$vendorDir = dirname($autoloadFile);
$autoloadRealPath = $vendorDir.'/composer/autoload_real.php';
$classLoaderPath = $vendorDir.'/composer/ClassLoader.php';

if (! $safeFileExists($autoloadRealPath) || ! $safeFileExists($classLoaderPath)) {
    if ($requestPath === '/' || $requestPath === '/index.php') {
        $renderWelcomeFallback();
    }

    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Composer vendor files are incomplete.\n";
    echo "Missing required bootstrap files under {$vendorDir}/composer.\n";
    echo "Expected:\n";
    echo " - {$autoloadRealPath}\n";
    echo " - {$classLoaderPath}\n";
    echo "Fix: run 'composer install --no-dev --optimize-autoloader' in {$basePath}\n";
    if ($contains($autoloadFile, '/vendor/vendor/')) {
        echo "Note: nested vendor folder detected. Move contents of {$basePath}/vendor/vendor into {$basePath}/vendor.\n";
    }
    exit;
}

try {
    require $autoloadFile;
} catch (\Throwable $e) {
    $isPlatformCheckError = $contains($e->getMessage(), 'Composer detected issues in your platform');

    if ($isPlatformCheckError && ($requestPath === '/' || $requestPath === '/index.php')) {
        $renderWelcomeFallback();
    }

    if ($isPlatformCheckError) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo "PHP version mismatch for this Laravel app.\n";
        echo "Current PHP version: ".PHP_VERSION."\n";
        echo "Composer dependencies require a newer PHP version (see composer.json require.php).\n";
        echo "Fix options:\n";
        echo " - Upgrade hosting PHP to 8.2+ (recommended for this project)\n";
        echo " - Or install dependencies compatible with your server PHP version\n";
        exit;
    }

    throw $e;
}

try {
    /** @var Application $app */
    $app = require_once $basePath.'/bootstrap/app.php';
    $app->handleRequest(Request::capture());
} catch (\Throwable $e) {
    if ($requestPath === '/' || $requestPath === '/index.php') {
        $renderWelcomeFallback();
    }

    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Application failed to boot.\n";
    echo get_class($e).": ".$e->getMessage()."\n";
    exit;
}
