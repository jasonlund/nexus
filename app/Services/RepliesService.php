<?php

namespace App\Services;

use App\Rules\RichTextRequired;
use Carbon\Carbon;

class RepliesService
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
            'body' => ['bail', new RichTextRequired, 'max:10000']
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

    /**
     * Create a Reply in the given Thread with the given data.
     *
     * @param   App\Models\Thread  $thread
     * @param   array   $data
     *
     * @return  App\Models\Reply
     */
    public function create($thread, $data)
    {
        return $thread->replies()->create([
            'body' => $data['body'],
            'user_id' => auth()->user()->id
        ]);
    }

    /**
     * Update a Reply with the given data.
     *
     * @param   App\Models\Reply  $reply
     * @param   array             $data
     *
     * @return  App\Models\Reply
     */
    public function update($reply, $data)
    {
        $reply->update([
            'body' => $data['body'],
            'edited_at' => Carbon::now(),
            'edited_by' => auth()->user()->id
        ]);

        return $reply;
    }

    /**
     * Deterimine if the given Reply has been viewed by the authenticated User.
     *
     * @param   App\Models\Reply  $reply
     *
     * @return  boolean
     */
    public function isNew($reply)
    {
        if (!auth()->check()) return false;

        $view = $reply->thread->viewedBy()->where('user_id', auth()->user()->id)->first();

        if (!$view) {
            return true;
        } else {
            return $reply->created_at >= $view->pivot->timestamp;
        }
    }
}
