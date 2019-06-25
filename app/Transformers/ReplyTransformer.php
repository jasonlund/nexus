<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Reply;
use App\Services\PurifyService;
use App\Services\RepliesService;

class ReplyTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'owner', 'editor'
    ];

    /**
     * Transform Replies.
     *
     * @param \App\Models\Reply $reply
     * @return array
     */
    public function transform(Reply $reply)
    {
//        $service = new RepliesService();
        $data = [
            'id' => (int) $reply->id,
            'body' => (string) PurifyService::clean($reply->body),
//            'new' => $service->isNew($reply),
            'created_at' => (string) $reply->created_at->format('Y-m-d H:i:s'),
            'updated_at' => (string) $reply->updated_at->format('Y-m-d H:i:s'),
            'edited_at' => $reply->edited_at ? $reply->edited_at->format('Y-m-d H:i:s') : null
        ];

        return $data;
    }

    public function includeOwner(Reply $reply)
    {
        return $this->item($reply->owner, new UserTransformer);
    }

    public function includeEditor(Reply $reply)
    {
        return $reply->editor ? $this->item($reply->editor, new UserTransformer) : $this->null();
    }
}
