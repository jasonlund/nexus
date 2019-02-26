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
            'body' => $reply->body
        ];

        if(app()->environment() === 'testing') $data['id'] = $reply->id;

        return $data;
    }

    public function includeOwner(Reply $reply)
    {
        return $this->item($reply->owner, new UserTransformer);
    }
}
