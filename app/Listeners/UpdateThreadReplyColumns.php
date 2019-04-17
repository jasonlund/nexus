<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateThreadReplyColumns
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if(get_class($event) === \App\Events\ReplyCreated::class){
            $event->reply->thread->latest_reply_at = $event->reply->created_at;
            $event->reply->thread->latest_reply_id = $event->reply->id;
        }else if(get_class($event) === \App\Events\ReplyDeleted::class){
            if($event->reply->thread->latest_reply_id === $event->reply->id){
                if($event->reply->thread->replies()->count() === 0) {
                    $event->reply->thread->latest_reply_at = null;
                    $event->reply->thread->latest_reply_id = null;
                }else{
                    $new = $event->reply->thread->replies->sortByDesc('created_at')->first();
                    $event->reply->thread->latest_reply_at = $new->created_at;
                    $event->reply->thread->latest_reply_id = $new->id;
                }
            }
        }

        $event->reply->thread->save();

    }
}
