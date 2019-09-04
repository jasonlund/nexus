<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param   Request       $request
     * @param   Closure       $next
     * @param   string|null   $guard
     *
     * @return  mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return response('Forbidden', 403);
        }

        return $next($request);
    }
}
