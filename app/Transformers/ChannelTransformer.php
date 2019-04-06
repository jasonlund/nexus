<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Channel;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class ChannelTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'threads'
    ];

    /**
     * Transform Channels.
     *
     * @param \App\Models\Channel $channel
     * @return array
     */
    public function transform(Channel $channel)
    {
        $data = [
            'order' => (int) $channel->order,
            'name' => (string) $channel->name,
            'slug' => (string) $channel->slug,
            'description' => (string) $channel->description,
            'moderators' => (array) $channel->moderators->sortBy('username')->pluck('username')->toArray(),
            'created_at' => (string) $channel->created_at->format('Y-m-d H:i:s'),
            'updated_at' => (string) $channel->updated_at->format('Y-m-d H:i:s'),
            'thread_count' => (int) $channel->threads()->count(),
            'reply_count' => (int) $channel->replies()->count()
        ];

        return $data;
    }
}
