<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Reply;
use Markdown;

class ReplyTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'owner'
    ];

    /**
     * Transform Replies.
     *
     * @param \App\Models\Reply $reply
     * @return array
     */
    public function transform(Reply $reply)
    {
        $data = [
            'id' => (int) $reply->id,
            'body' => (string) Markdown::convertToHtml($reply->body),
            'created_at' => (string) $reply->created_at->format('Y-m-d H:i:s'),
            'updated_at' => (string) $reply->updated_at->format('Y-m-d H:i:s')
        ];

        return $data;
    }

    public function includeOwner(Reply $reply)
    {
        return $this->item($reply->owner, new UserTransformer);
    }
}
