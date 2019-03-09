<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Reply;

class ReplyTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'owner'
    ];

    protected $defaultIncludes = [
        'owner'
    ];

    /**
     * A Fractal transformer.
     *
     * @param \App\Models\Reply $reply
     * @return array
     */
    public function transform(Reply $reply)
    {
        $data = [
            'id' => $reply->id,
            'body' => $reply->body,
            'created_at' => (string)$reply->created_at->format('Y-m-d H:i:s'),
            'updated_at' => (string)$reply->updated_at->format('Y-m-d H:i:s')
        ];

        return $data;
    }

    public function includeOwner(Reply $reply)
    {
        return $this->item($reply->owner, new UserTransformer);
    }
}
