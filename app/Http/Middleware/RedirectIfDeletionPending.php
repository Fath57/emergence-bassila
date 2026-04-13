<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfDeletionPending
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasPendingDeletion()) {
            return $next($request);
        }

        $allowed = [
            'account.deletion.cancel',
            'account.deletion.confirm',
            'logout',
        ];

        if (in_array($request->route()?->getName(), $allowed, true)) {
            return $next($request);
        }

        return redirect()->route('account.deletion.cancel');
    }
}
