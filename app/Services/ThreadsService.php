<?php

namespace App\Services;

use App\Events\ThreadViewed;
use App\Rules\RichTextRequired;
use Carbon\Carbon;
use App\Models\ViewedThread;
use App\Transformers\ReplyTransformer;
use App\Models\Thread;

class ThreadsService
{
    /**
     * Get the request validation rules given an optional action.
     *
     * @param   string|null  $action
     *
     * @return  array
     */
    public static function validationRules($action = null)
    {
        $rules = collect([
            'title' => ['bail', 'required', 'max:100'],
            'body' => ['bail',  new RichTextRequired, 'max:10000']
        ]);

        switch ($action) {
            case "create":
                $rules = $rules->only(['title', 'body']);
                break;
            case "update":
                $rules = $rules->only(['title', 'body']);
                break;
        }

        return $rules->toArray();
    }

    /**
     * Create a Thread in the given Channel with the given data.
     *
     * @param   App\Models\Channel  $channel
     * @param   array               $data
     *
     * @return  Thread
     */
    public function create($channel, $data)
    {
        return $channel->threads()->create([
            'title' => $data['title'],
            'body' => $data['body'],
            'user_id' => auth()->user()->id
        ]);
    }

    /**
     * Update the given Thread with the given data.
     *
     * @param   Thread  $thread
     * @param   array   $data
     *
     * @return  Thread
     */
    public function update($thread, $data)
    {
        $thread->update([
            'title' => $data['title'],
            'body' => $data['body'],
            'edited_at' => Carbon::now(),
            'edited_by' => auth()->user()->id
        ]);

        return $thread;
    }

    /**
     * Dispatch the ThreadViewed event for the authenticated User
     *
     * @param   Thread  $thread
     *
     * @return  void
     */
    public function show(Thread $thread)
    {
        if (auth()->check()) {
            event(new ThreadViewed($thread, auth()->user()));
        }
    }

    /**
     * Mark the given Thread as viewed for the authenticated User.
     *
     * @param   Thread           $thread
     * @param   App\Models\User  $user
     *
     * @return  void
     */
    public function viewed($thread, $user)
    {
        ViewedThread::updateOrCreate(
            ['user_id' => $user->id, 'thread_id' => $thread->id],
            ['timestamp' => Carbon::now()]
        );
    }

    /**
     * Determine wether the given Thread has unviewed Replies for the
     * authenticated user.
     *
     * @param   Thread  $thread
     *
     * @return  \Spatie\Fractal\Fractal|boolean
     */
    public function hasNewReplies($thread)
    {
        if (!auth()->check()) return false;

        $view = $thread->viewedBy()->where('user_id', auth()->user()->id)->first();

        if (!$view) {
            $reply = $thread->replies()->first();

            return $reply !== null ? fractal()
                ->item($reply)
                ->transformWith(new ReplyTransformer()) : true;
        } else if ($thread->latest_reply_at <= $view->pivot->timestamp) {
            return false;
        } else {
            $reply = $thread->replies()->where('created_at', '>=', $view->pivot->timestamp)->first();

            return $reply !== null ?
                fractal()
                ->item($reply)
                ->transformWith(new ReplyTransformer()) : true;
        }
    }

    /**
     * Toggle the locked status of the given Thread.
     *
     * @param   Thread  $thread
     *
     * @return  Thread
     */
    public function toggleLock($thread)
    {
        $thread->locked = !$thread->locked;
        $thread->save();

        return $thread;
    }
}
