<?php
namespace App\Services;

use App\Events\ThreadViewed;
use App\Rules\RichTextRequired;
use Carbon\Carbon;
use Cache;
use App\Models\ViewedThread;

class ThreadsService
{
    public static function validationRules($action = null)
    {
        $rules = collect([
            'title' => 'required',
            'body' => [ new RichTextRequired ]
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
            'body' => $data['body']
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
            return $thread->replies()->first() ?? true;
        }else if($thread->latest_reply_at < $view->pivot->timestamp){
            return false;
        }else{
            return $thread->replies()->where('created_at', '>', $view->pivot->timestamp)->first();
        }
    }
}
