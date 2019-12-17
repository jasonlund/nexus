<?php

namespace App\Transformers;

use App\Services\ChannelsService;
use League\Fractal\TransformerAbstract;
use App\Models\Channel;
use App\Services\PurifyService;
use Storage;
use Cache;

class ChannelTransformer extends TransformerAbstract
{
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

        if($channel->image_path) {
            $file_name = explode('.', $channel->image_path)[0];
            $images = [
                'full' => Storage::url($file_name . '.png'),
                '800' => Storage::url($file_name . '-800w.png'),
                '600' => Storage::url($file_name . '-600w.png'),
                'thumb' => Storage::url($file_name . '-thumb.png')
            ];
        }else{
            $images = null;
        }

        $latest = [
            'thread' => $channel->threads()->latest()->first(),
            'reply' => $channel->replies()->latest()->first(),
        ];

        if(!$latest['thread'] && !$latest['reply']) {
            $latest = null;
        }else if($latest['thread'] && !$latest['reply']) {
            $latest = $latest['thread']->created_at->format('Y-m-d H:i:s');
        }else{
            if($latest['thread']->created_at > $latest['reply']->created_at) {
                $latest = $latest['thread']->created_at->format('Y-m-d H:i:s');
            }else{
                $latest = $latest['reply']->created_at->format('Y-m-d H:i:s');
            }
        }

        $data = [
            'order' => (int) $channel->order,
            'name' => (string) $channel->name,
            'slug' => (string) $channel->slug,
            'description' => (string) PurifyService::simpleWithEmotes($channel->description),
            'image' => $images,
            'locked' => (bool) $channel->locked,
            'new' => $service->hasNewReplies($channel),
            'moderators' => (array) $channel->moderators->sortBy('username')->pluck('username')->toArray(),
            'created_at' => (string) $channel->created_at->format('Y-m-d H:i:s'),
            'updated_at' => (string) $channel->updated_at->format('Y-m-d H:i:s'),
            'thread_count' => (int) Cache::rememberForever('channel-thread-count-' . $channel->id, function() use ($channel) {
                return $channel->threads()->count();
            }),
            'reply_count' => (int) Cache::rememberForever('channel-reply-count-' . $channel->id, function() use ($channel) {
                return $channel->replies()->count();
            }),
            'latest_post' => $latest
        ];

        return $data;
    }
}
