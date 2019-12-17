<?php

namespace App\Listeners;

use App\Events\ThreadDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Cache;

class RemoveThreadReplyCache
{
    /**
     * Handle the event.
     *
     * @param  ThreadDeleted  $event
     * @return void
     */
    public function handle(ThreadDeleted $event)
    {
        Cache::remove('thread-reply-count-' . $event->thread);
    }
}
