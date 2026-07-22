<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Front Controller (Domain Root)
|--------------------------------------------------------------------------
| Used when the hosting document root points directly at this project
| folder (e.g. https://energy.infragovservices.com/) instead of at the
| /public subfolder. Base path is this directory itself.
*/

$basePath = __DIR__;

if (!file_exists($basePath.'/vendor/autoload.php')
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

$app->usePublicPath($basePath.'/public');

$app->handleRequest(Request::capture());
