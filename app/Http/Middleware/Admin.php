<?php

namespace App\Http\Middleware;

use Closure;
use Bouncer;

class Admin
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
        if(!auth()->check() || Bouncer::is(auth()->user())->notAn('admin')) {
            abort(403, 'Forbidden.');
        }
        return $next($request);
    }
}
