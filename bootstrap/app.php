<?php

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
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'registration.check' => \App\Http\Middleware\CheckRegistrationOpen::class,
        ]);

        // RFC 8058 one-click unsubscribe: mail clients POST directly, no session/CSRF
        $middleware->validateCsrfTokens(except: [
            'newsletter/desabonner/*',
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\MaintenanceModeCheck::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
