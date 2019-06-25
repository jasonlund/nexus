<?php
namespace App\Services;

use App\Events\ThreadViewed;
use App\Rules\RichTextRequired;
use Carbon\Carbon;
use Cache;
use App\Models\ViewedThread;
use App\Transformers\ReplyTransformer;

class ThreadsService
{
    public static function validationRules($action = null)
    {
        $rules = collect([
            'title' => ['required', 'max:100'],
            'body' => [ new RichTextRequired, 'max:10000' ]
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

    public function create($channel, $data)
    {
        return $channel->threads()->create([
            'title' => $data['title'],
            'body' => $data['body'],
            'user_id' => auth()->user()->id
        ]);
    }

    public function update($thread, $data)
    {
        return $thread->update([
            'title' => $data['title'],
            'body' => $data['body'],
            'edited_at' => Carbon::now(),
            'edited_by' => auth()->user()->id
        ]);
    }

    public function show($thread)
    {
        if (auth()->check()) {
            event(new ThreadViewed($thread, auth()->user()));
        }
    }

    public function viewed($thread, $user)
    {
        ViewedThread::updateOrCreate(
            ['user_id' => $user->id, 'thread_id' => $thread->id],
            ['timestamp' => Carbon::now()]
        );
    }

    public function hasNewReplies($thread)
    {
        if(!auth()->check()) return false;

        $view = $thread->viewedBy()->where('user_id', auth()->user()->id)->first();

        if(!$view) {
            $reply = $thread->replies()->first();

            return $reply !== null ? fractal()
                ->item($reply)
                ->transformWith(new ReplyTransformer()) : true;
        }else if($thread->latest_reply_at < $view->pivot->timestamp){
            return false;
        }else{
            $reply = $thread->replies()->where('created_at', '>', $view->pivot->timestamp)->first();

            return $reply !== null ? fractal()
                ->item($reply)
                ->transformWith(new ReplyTransformer()) : true;
        }
    }

    public function toggleLock($thread)
    {
        $thread->locked = !$thread->locked;
        $thread->save();

        return $thread;
    }
}
