<?php

namespace App\Transformers;

use App\Services\ChannelsService;
use League\Fractal\TransformerAbstract;
use App\Models\Channel;
use App\Services\PurifyService;

class ChannelTransformer extends TransformerAbstract
{
    /**
     * The relationships that are available for inclusion
     *
     * @var array
     */
    protected $availableIncludes = [
        'latest_thread', 'latest_reply'
    ];

    /**
     * Transform Channels.
     *
     * @param   Channel  $channel
     *
     * @return  array
     */
    public function transform(Channel $channel)
    {
        $service = new ChannelsService();

        $data = [
            'order' => (int) $channel->order,
            'name' => (string) $channel->name,
            'slug' => (string) $channel->slug,
            'description' => (string) PurifyService::simple($channel->description),
            'locked' => (bool) $channel->locked,
            'new' => $service->hasNewReplies($channel),
            'moderators' => (array) $channel->moderators->sortBy('username')->pluck('username')->toArray(),
            'created_at' => (string) $channel->created_at->format('Y-m-d H:i:s'),
            'updated_at' => (string) $channel->updated_at->format('Y-m-d H:i:s'),
            'thread_count' => (int) $channel->threads()->count(),
            'reply_count' => (int) $channel->replies()->count()
        ];

        return $data;
    }

    /**
     * Include the latest Thread if it exists.
     *
     * @param   Channel  $channel
     *
     * @return  \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */
    public function includeLatestThread(Channel $channel)
    {
        return $channel->threads()->count() !== 0 ?
            $this->item($channel->threads()->latest()->first(), new ThreadTransformer()) : null;
    }

    /**
     * Include the latest Reply if it exists.
     *
     * @param   Channel  $channel
     *
     * @return  \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */
    public function includeLatestReply(Channel $channel)
    {
        return $channel->replies()->count() !== 0 ?
            $this->item($channel->replies()->latest()->first(), new ReplyTransformer()) : null;
    }
}
