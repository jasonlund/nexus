<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\ChannelCategory;
use App\Models\User;
use App\Rules\RichTextRequired;
use App\Services\ThreadsService;

class ChannelsService
{
    /**
     * Get the request validation rules given an optional action.
     *
     * @param   string|null  $action
     *
     * @return  array
     */
    public static function validationRules($action = null)
    {
        $rules = collect([
            'name' => ['bail', 'required', 'string', 'max:100'],
            'description' => ['bail', new RichTextRequired, 'max:1000'],
            'moderators' => ['bail', 'array', 'exists:users,username'],
            'order' => ['bail', 'required', 'array', 'exists:channels,slug'],
            'locked' => ['bail', 'sometimes', 'boolean'],
            'channel_category' => ['bail', 'required', 'exists:channel_categories,slug']
        ]);

        switch ($action) {
            case "create":
                $rules = $rules->only([
                    'name', 'description', 'moderators', 'locked'
                ]);
                break;
            case "update":
                $rules = $rules->only([
                    'name', 'description', 'channel_category', 'moderators', 'locked'
                ]);
                break;
            case "reorder":
                $rules = $rules->only('order');
                break;
        }

        return $rules->toArray();
    }

    /**
     * Create a new Channel.
     *
     * @param   ChannelCategory  $category
     * @param   array            $data
     *
     * @return  Channel
     */
    public function create(ChannelCategory $category, $data)
    {
        return $category->channels()->create([
            'name' => $data['name'],
            'description' => $data['description'],
            'locked' => $data['locked'] ?? false
        ]);
    }

    /**
     * Update an exisiting channel.
     *
     * @param   Channel  $channel
     * @param   array    $data
     *
     * @return  Channel
     */
    public function update($channel, $data)
    {
        $category = ChannelCategory::where('slug', $data['channel_category'])->first();
        $currentCategory = $channel->category;

        $channel->update([
            'name' => $data['name'],
            'description' => $data['description'],
            'locked' => $data['locked'] ?? false,
            'channel_category_id' => $category->id
        ]);

        if($category->id !== $currentCategory->id){
            Channel::setNewOrder($category->channels()->ordered()->pluck('id')->toArray());
            Channel::setNewOrder($currentCategory->channels()->ordered()->pluck('id')->toArray());
        }

        return $channel;
    }

    public function destroy($channel)
    {
        $category = $channel->category;

        $channel->delete();

        Channel::setNewOrder($category->channels()->ordered()->pluck('id')->toArray());
    }

    /**
     * Assign the given Users as moderators to the given Channel.
     *
     * @param   Channel  $channel
     * @param   array    $users
     *
     * @return  Channel
     */
    public function assignModerators($channel, $users)
    {
        if (!$users || !count($users)) {
            $channel->moderators()->sync([]);
        } else {
            $users = User::whereIn('username', $users)->get();
            $channel->moderators()->sync($users);
        }

        return $channel;
    }

    /**
     * Reorder all Channels.
     *
     * @param   array  $order
     *
     * @return  void
     */
    public function reorder($order)
    {
        $channels = Channel::whereIn('slug', $order)->get()
            ->sortBy(function ($item) use ($order) {
                return array_search($item->slug, $order);
            });

        Channel::setNewOrder($channels->pluck('id')->toArray());
    }

    /**
     * Mark all Threads in the given Channel as viewed
     *
     * @param   Channel  $channel
     *
     * @return  Channel
     */
    public function viewed($channel)
    {
        $service = new ThreadsService();
        $threads = $channel->threads()->get();
        $user = auth()->user();

        foreach ($threads as $thread) {
            $service->viewed($thread, $user);
        }

        return $channel;
    }

    /**
     * Determine if the authenticated User has any unviewed Replies in the given Channel.
     *
     * @param   Channel  $channel
     *
     * @return  boolean
     */
    public function hasNewReplies($channel)
    {
        if (!auth()->check() || !$channel->threads()->count()) return false;

        $view = $channel->viewedBy()->where('users.id', auth()->user()->id)->get();

        if (
            $view->count() === 0 || $view->count() !== $channel->threads()->count()
        ) return true;

        $result = $view->search(function ($item) {
            return $item->viewed_thread->timestamp < $item->viewed_thread->thread->latest_reply_at;
        });

        return $result !== false;
    }
}
