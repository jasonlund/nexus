<?php

namespace App\Http\Middleware;

use Closure;
use Guard;
use App\Models\User;

class ForbidBannedUser
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if(!$user && request()->routeIs('auth.login', 'password.email', 'password.update')) {
            $user = User::where('email', request('email'))->first();
        }

        if ($user && $user->isBanned()) {
            $ban = $user->bans()->latest()->first();

            auth()->logout();

            $message = 'Account ' . $ban->isPermanent() ? 'permanently banned' : ' temporarily banned until ' .
                $ban->expires_at->diffForHumans() . $ban->comment ? '. Reason: ' . $ban->comment . '.' : '.';

            if($request->expectsJson()) {
                abort(403, $message);
            }else{
                \Session::put('ban-reason', $message);

                return redirect()->route('home')->withInput()->withErrors([
                    'login' => $message,
                ]);
            }
        }

        return $next($request);
    }
}
