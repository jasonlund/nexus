<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Reply;
use App\Services\PurifyService;

class ReplyTransformer extends TransformerAbstract
{
    /**
     * The relationships that are available for inclusion
     *
     * @var array
     */
    protected $availableIncludes = [
        'owner', 'editor'
    ];

    /**
     * Transform Replies.
     *
     * @param   Reply  $reply
     *
     * @return  array
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

    /**
     * Include the owner (User).
     *
     * @param   Reply  $reply
     *
     * @return  \League\Fractal\Resource\Item
     */
    public function includeOwner(Reply $reply)
    {
        return $this->item($reply->owner, new UserTransformer);
    }

    /**
     * Include the editor (User) if it exists.
     *
     * @param   Reply  $reply
     *
     * @return  \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */
    public function includeEditor(Reply $reply)
    {
        return $reply->editor ? $this->item($reply->editor, new UserTransformer) : $this->null();
    }
}
