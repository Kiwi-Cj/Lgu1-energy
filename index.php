<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

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

$isPathAllowed = static function (?string $path) use ($openBaseDirs, $normalizePath): bool {
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
            str_starts_with($normalizedPath.'/', $allowedPath.'/')
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
foreach (array_values(array_unique($candidatePaths)) as $candidate) {
    $checkedPaths[] = $candidate;

    $autoloadPath = $candidate.'/vendor/autoload.php';
    $bootstrapPath = $candidate.'/bootstrap/app.php';

    if ($safeFileExists($autoloadPath) && $safeFileExists($bootstrapPath)) {
        $basePath = $candidate;
        break;
    }

    if (
        $safeFileExists($bootstrapPath) ||
        $safeIsDir($candidate.'/bootstrap') ||
        $safeIsDir($candidate.'/vendor') ||
        $safeFileExists($candidate.'/public/index.php')
    ) {
        $laravelLikePaths[] = sprintf(
            '%s (bootstrap/app.php: %s, vendor/autoload.php: %s)',
            $candidate,
            $safeFileExists($bootstrapPath) ? 'found' : 'missing',
            $safeFileExists($autoloadPath) ? 'found' : 'missing'
        );
    }
}

if ($basePath === null) {
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

require $basePath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
