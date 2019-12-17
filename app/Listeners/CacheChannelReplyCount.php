<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Cache;

class CacheChannelReplyCount
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        Cache::forever('channel-reply-count-' . $event->reply->channel->id, $event->reply->channel->replies()->count());
    }
}
