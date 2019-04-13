<?php
namespace App\Services;

use App\Rules\RichTextRequired;

class RepliesService
{
    public static function validationRules($action = null)
    {
        $rules = collect([
            'body' => [ new RichTextRequired ]
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
            'body' => $data['body']
        ]);
    }
}
