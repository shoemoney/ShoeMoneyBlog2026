<?php

use App\Http\Middleware\TrailingSlashRedirect;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // TrailingSlashRedirect must run before routing to catch URLs
        // without trailing slashes before they hit route matching
        $middleware->prepend(TrailingSlashRedirect::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
