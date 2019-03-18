<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Thread;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class ThreadTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'owner', 'replies'
    ];

    /**
     * Transform Threads.
     *
     * @param \App\Models\Thread $thread
     * @return array
     */
    public function transform(Thread $thread)
    {
        $data = [
            'title' => (string) $thread->title,
            'slug' => (string) $thread->slug,
            'body' => (string) $thread->body,
            'reply_count' => (int) $thread->replies()->count(),
            'created_at' => (string) $thread->created_at->format('Y-m-d H:i:s'),
            'updated_at' => (string) $thread->updated_at->format('Y-m-d H:i:s')
        ];

        return $data;
    }

    public function includeOwner(Thread $thread)
    {
        return $this->item($thread->owner, new UserTransformer);
    }
}
