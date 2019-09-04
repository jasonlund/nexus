<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\UsersService;
use Illuminate\Http\Request;

class LastActive
{
    /**
     * Log the activity of an authenticated User.
     *
     * @param   Request  $request
     * @param   Closure  $next
     *
     * @return  Closure
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        (new UsersService())->logActive(auth()->user());
        return $next($request);
    }
}
