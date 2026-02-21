<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$basePath = __DIR__;

if (! file_exists($basePath.'/vendor/autoload.php') || ! file_exists($basePath.'/bootstrap/app.php')) {
    $parentPath = dirname(__DIR__);
    if (file_exists($parentPath.'/vendor/autoload.php') && file_exists($parentPath.'/bootstrap/app.php')) {
        $basePath = $parentPath;
    }
}

if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

if (! file_exists($basePath.'/vendor/autoload.php')) {
    http_response_code(500);
    exit('Laravel autoload not found. Check deployment paths and run composer install.');
}

require $basePath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
