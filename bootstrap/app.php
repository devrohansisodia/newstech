<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use NewsTech\Admin\Http\Middleware\RedirectIfAdminAuthenticated;
use NewsTech\Admin\Http\Middleware\RequireAdminAuthentication;
use NewsTech\Core\Http\Middleware\ApplySystemSettings;
use NewsTech\Reader\Http\Middleware\RedirectIfReaderAuthenticated;
use NewsTech\Reader\Http\Middleware\RequireReaderAuthentication;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            ApplySystemSettings::class,
        ]);

        $middleware->alias([
            'admin.auth' => RequireAdminAuthentication::class,
            'admin.guest' => RedirectIfAdminAuthenticated::class,
            'reader.auth' => RequireReaderAuthentication::class,
            'reader.guest' => RedirectIfReaderAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
