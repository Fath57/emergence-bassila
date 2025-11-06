<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->is_approved && !auth()->user()->is_admin) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Votre compte est en attente de validation par un administrateur.');
        }

        return $next($request);
    }
}
