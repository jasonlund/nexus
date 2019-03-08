<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Bouncer;
use App\Models\Channel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Bouncer::ownedVia(Channel::class, function ($channel, $user) {
            return $channel->moderators->where('id', $user->id)->count() !== 0;
        });
    }
}
