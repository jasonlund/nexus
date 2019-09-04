<?php

namespace App\Http\Middleware;

use Closure;
use Cache;
use Bouncer;

class LimitActions
{
    /**
     * Check the authenticated user's last activity timestamp and deny the request if applicable.
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

        $user = auth()->user();

        if(Bouncer::can('unlimited-actions')) {
            return $next($request);
        }

        if(!Cache::has('last-user-action-' . $user->id)) {
            Cache::forever('last-user-action-' . $user->id, now());
            return $next($request);
        }

        $lastActive = Cache::get('last-user-action-' . $user->id);

        if($lastActive->diffInSeconds(now()) <= 30) {
            abort(429, 'Too many requests. Please wait 30 seconds before trying again.');
        }

        Cache::forever('last-user-action-' . $user->id, now());

        return $next($request);
    }
}
