<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$explicitBasePath = getenv('LARAVEL_BASE_PATH') ?: ($_SERVER['LARAVEL_BASE_PATH'] ?? null);
$publicPath = __DIR__;
$parentPath = dirname(__DIR__);
$checkedPaths = [];
$candidatePaths = array_filter([
    $explicitBasePath,
    $publicPath,
    $parentPath,
    $publicPath.'/laravel',
    $publicPath.'/laravel_app',
    $publicPath.'/app',
    $parentPath.'/laravel',
    $parentPath.'/laravel_app',
    $parentPath.'/app',
]);

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

// Scan one more level for common shared-hosting layouts like:
// /home/user/repositories/project or /home/user/public_html/project
$levelOneDirs = array_values(array_unique(array_filter($candidatePaths, 'is_dir')));
foreach ($levelOneDirs as $dir) {
    foreach (glob($dir.'/*', GLOB_ONLYDIR) ?: [] as $subdir) {
        $candidatePaths[] = $subdir;
    }
}

$basePath = null;
foreach (array_values(array_unique($candidatePaths)) as $candidate) {
    $checkedPaths[] = $candidate;
    if (file_exists($candidate.'/vendor/autoload.php') && file_exists($candidate.'/bootstrap/app.php')) {
        $basePath = $candidate;
        break;
    }
}

if ($basePath === null) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Laravel bootstrap files not found.\n";
    echo "Set LARAVEL_BASE_PATH to your Laravel app directory.\n";
    echo "Checked paths:\n - ".implode("\n - ", $checkedPaths);
    exit;
}

if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $basePath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
