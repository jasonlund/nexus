<?php

namespace App\Providers;

use Bouncer;
use App\Models\Emote;
use App\Models\Channel;
use App\Models\ChannelCategory;
use App\Models\User;
use App\Models\Thread;
use App\Models\Reply;
use App\Observers\ChannelObserver;
use App\Observers\ChannelCategoryObserver;
use App\Observers\EmoteObserver;
use App\Observers\UserObserver;
use App\Observers\ThreadObserver;
use App\Observers\ReplyObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return  void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return  void
     */
    public function boot()
    {
        /**
         * A User can moderate specific Channels.
         */
        Bouncer::ownedVia(Channel::class, function ($channel, $user) {
            return $channel->moderators->where('id', $user->id)->count() !== 0;
        });

        /**
         * Register Model Observers.
         */
        ChannelCategory::observe(ChannelCategoryObserver::class);
        Emote::observe(EmoteObserver::class);
        Reply::observe(ReplyObserver::class);
        Thread::observe(ThreadObserver::class);
        User::observe(UserObserver::class);
    }
}
