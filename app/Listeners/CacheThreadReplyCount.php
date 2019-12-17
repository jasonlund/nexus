<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Cache;

class CacheThreadReplyCount
{
    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function handle($event)
    {
        Cache::forever('thread-reply-count-' . $event->reply->thread->id,
            $event->reply->thread->replies()->count());
    }
}
