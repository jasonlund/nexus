<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\UsersService;

class LastActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        (new UsersService())->logActive(auth()->user());
        return $next($request);
    }
}
