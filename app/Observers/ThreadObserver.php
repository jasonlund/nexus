<?php

namespace App\Observers;

use Cache;

class ThreadObserver
{
    public function created($model)
    {
        $this->cacheChannelCounts($model);
        Cache::forever('thread-reply-count-' . $model->id, 0);
    }

    public function deleted($model)
    {
        $this->cacheChannelCounts($model);
        Cache::forget('thread-reply-count-' . $model->id);
    }

    private function cacheChannelCounts($model)
    {
        Cache::forever('channel-thread-count-' . $model->channel->id,
            $model->channel->threads()->count());
        Cache::forever('user-thread-count-' . $model->owner->id, $model->owner->threads()->count());
    }
}
