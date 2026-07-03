<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\StaffMiddleware::class);
        $middleware->append(\App\Http\Middleware\AuditTrailMiddleware::class);
        // This needs the web session to be started before it can resolve the
        // logged-in user and load that user's bell notifications.
        $middleware->web(append: [
            \App\Http\Middleware\ShareLayoutData::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'staff' => \App\Http\Middleware\StaffMiddleware::class,
            'block.staff.reports' => \App\Http\Middleware\BlockStaffFromReports::class,
            'download.confirmed' => \App\Http\Middleware\RequireDownloadAuthorization::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
