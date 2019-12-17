<?php

namespace App\Observers;

use Cache;

class ReplyObserver
{
    public function created($model)
    {
        $model->thread->latest_reply_at = $model->created_at;
        $model->thread->latest_reply_id = $model->id;
        $model->thread->save();

        $this->cacheCounts($model);
    }

    public function deleted($model)
    {
        if ($model->thread->latest_reply_id === $model->id) {
            if ($model->thread->replies()->count() === 0) {
                $model->thread->latest_reply_at = null;
                $model->thread->latest_reply_id = null;
            } else {
                $new = $model->thread->replies->sortByDesc('created_at')->first();
                $model->thread->latest_reply_at = $new->created_at;
                $model->thread->latest_reply_id = $new->id;
            }
        }
        $model->thread->save();

        $this->cacheCounts($model);
    }

    private function cacheCounts($model)
    {
        Cache::forever('channel-reply-count-' . $model->channel->id, $model->channel->replies()->count());
        Cache::forever('thread-reply-count-' . $model->thread->id, $model->thread->replies()->count());
        Cache::forever('user-reply-count-' . $model->owner->id, $model->owner->replies()->count());
    }
}
