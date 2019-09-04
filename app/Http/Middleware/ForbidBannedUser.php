<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;

class ForbidBannedUser
{
    /**
     * Check if the authenticated user is banned and return a Forbidden response
     * if true.
     *
     * @param   Request  $request  [$request description]
     * @param   Closure  $next     [$next description]
     *
     * @return  mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user && request()->routeIs('auth.login', 'password.email', 'password.reset')) {
            $user = User::where('email', request('email'))->first();
        }

        if ($user && $user->isBanned()) {
            $ban = $user->bans()->latest()->first();

            if (auth()->check())
                auth()->logout();

            if ($ban->isPermanent()) {
                $message = 'Account permanently banned.';
            } else {
                $message = 'Account temporarily banned until ' . $ban->expired_at->diffForHumans() . '.';
            }

            if ($ban->comment) {
                $message .= ' Reason: ' . $ban->comment;
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
