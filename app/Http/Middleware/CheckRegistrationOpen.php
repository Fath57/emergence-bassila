<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRegistrationOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (setting('site.registration_open', true)) {
            return $next($request);
        }

        return redirect()
            ->route('login')
            ->with('error', 'Les inscriptions sont temporairement fermées.');
    }
}
