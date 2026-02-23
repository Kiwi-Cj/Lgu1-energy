<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Set Laravel Base Path (Shared Hosting Setup)
|--------------------------------------------------------------------------
| Laravel folder:
| /home/energy.infragovservices.com/lgu1_energy
|
*/

$basePath = realpath(__DIR__ . '/../lgu1_energy');

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