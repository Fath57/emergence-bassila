<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceModeCheck
{
    /** @var string[] Exact paths always allowed through maintenance mode. */
    private const ALLOW_PATHS = ['connexion', 'up'];

    /** @var string[] Path prefixes always allowed through maintenance mode. */
    private const ALLOW_PREFIXES = ['admin', 'livewire', 'build'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! setting('site.maintenance_mode', false)) {
            return $next($request);
        }

        // Admins always pass through so they can still sign in and manage the site.
        if ($request->user()?->can('admin.access')) {
            return $next($request);
        }

        $path = $request->path();

        if (in_array($path, self::ALLOW_PATHS, true)) {
            return $next($request);
        }

        foreach (self::ALLOW_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $next($request);
            }
        }

        return response()->view('maintenance', [
            'message' => setting('site.maintenance_message', 'Site temporairement indisponible.'),
        ], 503);
    }
}
