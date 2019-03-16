<?php

namespace App\Http\Middleware;

use Closure;
use Guard;
use App\Models\User;

class ForbidBannedUser
{
    /**
     * Check if the authenticated user is banned and return a Forbidden response if true.
     *
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if(!$user && request()->routeIs('auth.login', 'password.email', 'password.update')) {
            $user = User::where('email', request('email'))->first();
        }

        if ($user && $user->isBanned()) {
            $ban = $user->bans()->latest()->first();

            if(auth()->checK()) auth()->logout();

            if($ban->isPermanent()){
                $message = 'Account permanently banned.';
            }else{
                $message = 'Account temporarily banned until ' . $ban->expired_at->diffForHumans() . '.';
            }

            if($ban->comment) {
                $message .= ' Reason: ' . $ban->comment;
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
