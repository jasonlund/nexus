<?php

namespace App\Listeners;

class UpdateThreadReplyColumns
{
    /**
     * Update the Thread's latest reply column when a reply is created or
     * deleted.
     *
     * @param   mixed  $event
     *
     * @return  void
     */
    public function handle($event)
    {
        if (get_class($event) === \App\Events\ReplyCreated::class) {
            $event = $this->handleCreated($event);
        } else if (get_class($event) === \App\Events\ReplyDeleted::class) {
            $event = $this->handleDeleted($event);
        }

        $event->reply->thread->save();
    }

    /**
     * Update the Thread's latest reply column when a new Reply is created.
     *
     * @param   \App\Events\ReplyCreated  $event
     *
     * @return  \App\Events\ReplyCreated
     */
    private function handleCreated($event)
    {
        $event->reply->thread->latest_reply_at = $event->reply->created_at;
        $event->reply->thread->latest_reply_id = $event->reply->id;

        return $event;
    }

    /**
     * Update the Thread's latest reply column when a Reply is deleted.
     *
     * @param   \App\Events\ReplyDeleted  $event
     *
     * @return  \App\Events\ReplyDeleted
     */
    private function handleDeleted($event)
    {
        if ($event->reply->thread->latest_reply_id === $event->reply->id) {
            if ($event->reply->thread->replies()->count() === 0) {
                $event->reply->thread->latest_reply_at = null;
                $event->reply->thread->latest_reply_id = null;
            } else {
                $new = $event->reply->thread->replies->sortByDesc('created_at')->first();
                $event->reply->thread->latest_reply_at = $new->created_at;
                $event->reply->thread->latest_reply_id = $new->id;
            }
        }

        return $event;
    }
}
