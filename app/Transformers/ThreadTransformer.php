<?php

namespace App\Transformers;

use App\Services\ThreadsService;
use League\Fractal\TransformerAbstract;
use App\Models\Thread;
use App\Services\PurifyService;
use Illuminate\Support\Str;

class ThreadTransformer extends TransformerAbstract
{
    /**
     * The relationships that are available for inclusion
     *
     * @var array
     */
    protected $availableIncludes = [
        'owner', 'latest_reply', 'editor'
    ];

    /**
     * Transform Threads.
     *
     * @param   Thread  $thread
     *
     * @return  array
     */
    public function transform(Thread $thread)
    {
        $service = new ThreadsService();
        $data = [
            'title' => (string) $thread->title,
            'slug' => (string) $thread->slug,
            'body' => (string) PurifyService::clean($thread->body),
            'locked' => (bool) $thread->locked,
            'replies' => $thread->replies()->pluck('id'),
            'reply_count' => (int) $thread->replies()->count(),
            'new' => $service->hasNewReplies($thread),
            'created_at' => (string) $thread->created_at->format('Y-m-d H:i:s'),
            'updated_at' => (string) $thread->updated_at->format('Y-m-d H:i:s'),
            'edited_at' => $thread->edited_at ? $thread->edited_at->format('Y-m-d H:i:s') : null
        ];

        return $data;
    }

    /**
     * Include the owner (User).
     *
     * @param   Thread  $thread
     *
     * @return  \League\Fractal\Resource\Item
     */
    public function includeOwner(Thread $thread)
    {
        return $this->item($thread->owner, new UserTransformer);
    }

    /**
     * Inlude the editor (User) if it exists.
     *
     * @param   Thread  $thread
     *
     * @return  \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */
    public function includeEditor(Thread $thread)
    {
        return $thread->editor ? $this->item($thread->editor, new UserTransformer) : $this->null();
    }

    /**
     * Include the latest Reply if it exists.
     *
     * @param   Thread  $thread
     *
     * @return  \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */
    public function includeLatestReply(Thread $thread)
    {
        return $thread->latestReply ? $this->item($thread->latestReply, new ReplyTransformer) : $this->null();
    }
}
