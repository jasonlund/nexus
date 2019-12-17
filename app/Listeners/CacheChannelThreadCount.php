<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Cache;

class CacheChannelThreadCount
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        Cache::forever('channel-thread-count-' . $event->thread->channel->id,
            $event->thread->channel->threads()->count());
    }
}
