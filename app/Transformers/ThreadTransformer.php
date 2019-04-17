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
        'owner', 'latest_reply'
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
            'reply_count' => (int) $thread->replies()->count(),
            'new' => $service->hasNewReplies($thread),
            'created_at' => (string) $thread->created_at->format('Y-m-d H:i:s'),
            'updated_at' => (string) $thread->updated_at->format('Y-m-d H:i:s')
        ];

        return $data;
    }

    public function includeOwner(Thread $thread)
    {
        return $this->item($thread->owner, new UserTransformer);
    }

    public function includeLatestReply(Thread $thread)
    {
        return $thread->latestReply ? $this->item($thread->latestReply, new ReplyTransformer) : $this->null();
    }
}
