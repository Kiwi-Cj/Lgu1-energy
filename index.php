<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Set Laravel Base Path (Local Setup)
|--------------------------------------------------------------------------
| Local project root (one level above /public)
*/

$basePath = realpath(__DIR__ . '/..');

if ($basePath === false
    || !file_exists($basePath.'/vendor/autoload.php')
    || !file_exists($basePath.'/bootstrap/app.php')) {

    http_response_code(500);
    exit('Laravel base path not found. Check folder name.');
}

/*
|--------------------------------------------------------------------------
| Maintenance Mode
|--------------------------------------------------------------------------
*/

if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register Autoloader
|--------------------------------------------------------------------------
*/

require $basePath.'/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Bootstrap Laravel
|--------------------------------------------------------------------------
*/

$app = require_once $basePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
