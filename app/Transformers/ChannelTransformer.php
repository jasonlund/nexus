<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Channel;

class ChannelTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'threads'
    ];

    /**
     * A Fractal transformer.
     *
     * @param \App\Models\Channel $channel
     * @return array
     */
    public function transform(Channel $channel)
    {
        $data = [
            'name' => (string) $channel->name,
            'slug' => (string) $channel->slug,
            'description' => (string) $channel->description,
            'created_at' => $channel->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $channel->updated_at->format('Y-m-d H:i:s'),
            'thread_count' => $channel->threads()->count(),
            'reply_count' => $channel->replies()->count()
        ];

        return $data;
    }

    public function includeThreads(Channel $channel)
    {
        return $this->collection($channel->threads, new ThreadTransformer);
    }
}
