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

$pushCandidate = static function (?string $path) use (&$candidatePaths): void {
    if (! is_string($path)) {
        return;
    }

    $path = trim($path);
    if ($path === '') {
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

if (is_dir($parentPath)) {
    foreach (glob($parentPath.'/*', GLOB_ONLYDIR) ?: [] as $dir) {
        $candidatePaths[] = $dir;
    }
}

if (is_dir($publicPath)) {
    foreach (glob($publicPath.'/*', GLOB_ONLYDIR) ?: [] as $dir) {
        $candidatePaths[] = $dir;
    }
}

$levelOneDirs = array_values(array_unique(array_filter($candidatePaths, 'is_dir')));
foreach ($levelOneDirs as $dir) {
    foreach (glob($dir.'/*', GLOB_ONLYDIR) ?: [] as $subdir) {
        $candidatePaths[] = $subdir;
    }
}

foreach (array_values(array_unique($candidatePaths)) as $dir) {
    if (! is_dir($dir)) {
        continue;
    }

    if (is_dir($dir.'/current')) {
        $candidatePaths[] = $dir.'/current';
    }

    if (is_dir($dir.'/releases')) {
        foreach (glob($dir.'/releases/*', GLOB_ONLYDIR) ?: [] as $releaseDir) {
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

    if (file_exists($autoloadPath) && file_exists($bootstrapPath)) {
        $basePath = $candidate;
        break;
    }

    if (
        file_exists($bootstrapPath) ||
        is_dir($candidate.'/bootstrap') ||
        is_dir($candidate.'/vendor') ||
        file_exists($candidate.'/public/index.php')
    ) {
        $laravelLikePaths[] = sprintf(
            '%s (bootstrap/app.php: %s, vendor/autoload.php: %s)',
            $candidate,
            file_exists($bootstrapPath) ? 'found' : 'missing',
            file_exists($autoloadPath) ? 'found' : 'missing'
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

if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $basePath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
