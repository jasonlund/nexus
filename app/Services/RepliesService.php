<?php
namespace App\Services;

use App\Rules\RichTextRequired;
use Carbon\Carbon;

class RepliesService
{
    public static function validationRules($action = null)
    {
        $rules = collect([
            'body' => [ new RichTextRequired, 'max:10000' ]
        ]);

        switch ($action) {
            case "create":
                $rules = $rules->only('body');
                break;
            case "update":
                $rules = $rules->only('body');
                break;
        }

        return $rules->toArray();
    }

    public function create($thread, $data)
    {
        return $thread->replies()->create([
            'body' => $data['body'],
            'user_id' => auth()->user()->id
        ]);
    }

    public function update($reply, $data)
    {
        return $reply->update([
            'body' => $data['body'],
            'edited_at' => Carbon::now(),
            'edited_by' => auth()->user()->id
        ]);
    }

    public function isNew($reply)
    {
        if(!auth()->check()) return false;

        $view = $reply->thread->viewedBy()->where('user_id', auth()->user()->id)->first();

        if(!$view) {
            return true;
        }else{
            return $reply->created_at >= $view->pivot->timestamp;
        }
    }
}
