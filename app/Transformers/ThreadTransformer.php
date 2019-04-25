<?php

namespace App\Transformers;

use App\Services\ThreadsService;
use League\Fractal\TransformerAbstract;
use App\Models\Thread;
use Purify;

class ThreadTransformer extends TransformerAbstract
{
    protected $service;

    protected $availableIncludes = [
        'owner', 'latest_reply', 'editor'
    ];

    /**
     * Transform Threads.
     *
     * @param \App\Models\Thread $thread
     * @return array
     */
    public function transform(Thread $thread)
    {
        $service = new ThreadsService();
        $data = [
            'title' => (string) $thread->title,
            'slug' => (string) $thread->slug,
            'body' => (string) Purify::clean($thread->body),
            'locked' => (boolean) $thread->locked,
            'reply_count' => (int) $thread->replies()->count(),
            'new' => $service->hasNewReplies($thread),
            'created_at' => (string) $thread->created_at->format('Y-m-d H:i:s'),
            'updated_at' => (string) $thread->updated_at->format('Y-m-d H:i:s'),
            'edited_at' => $thread->edited_at ? $thread->edited_at->format('Y-m-d H:i:s') : null
        ];

        return $data;
    }

    public function includeOwner(Thread $thread)
    {
        return $this->item($thread->owner, new UserTransformer);
    }

    public function includeEditor(Thread $thread)
    {
        return $thread->editor ? $this->item($thread->editor, new UserTransformer) : $this->null();
    }

    public function includeLatestReply(Thread $thread)
    {
        return $thread->latestReply ? $this->item($thread->latestReply, new ReplyTransformer) : $this->null();
    }
}
