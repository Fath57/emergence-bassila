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
        // Trust Dokku's nginx reverse proxy so Laravel picks up X-Forwarded-Proto
        // and generates HTTPS URLs when the client-facing request is HTTPS.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'registration.check' => \App\Http\Middleware\CheckRegistrationOpen::class,
            'deletion.redirect' => \App\Http\Middleware\RedirectIfDeletionPending::class,
        ]);

        // RFC 8058 one-click unsubscribe: mail clients POST directly, no session/CSRF
        $middleware->validateCsrfTokens(except: [
            'newsletter/desabonner/*',
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\MaintenanceModeCheck::class,
            \App\Http\Middleware\RedirectIfDeletionPending::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
