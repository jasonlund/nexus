<?php

namespace App\Observers;

use Cache;

class UserObserver
{
    public function created($model)
    {
        Cache::forever('user-reply-count-' . $model->id, 0);
        Cache::forever('user-thread-count-' . $model->id, 0);
    }

    public function deleting($model)
    {
        Cache::forget('user-reply-count-' . $model->id);
        Cache::forget('user-thread-count-' . $model->id);

        $model->email = $model->email . '.deleted';
        $model->save();
    }
}
