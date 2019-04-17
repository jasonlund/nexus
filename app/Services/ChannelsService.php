<?php
namespace App\Services;

use App\Models\Channel;
use App\Models\User;
use Cache;
use App\Models\ViewedThread;
use Carbon\Carbon;
use \App\Services\ThreadsService;

class ChannelsService
{
    public static function validationRules($action = null)
    {
        $rules = collect([
            'name' => 'required|string',
            'description' => 'required|string',
            'moderators' => 'array|exists:users,username',
            'order' => 'required|array|exists:channels,slug',
        ]);

        switch ($action) {
            case "create":
                $rules = $rules->only(['name', 'description', 'moderators']);
                break;
            case "update":
                $rules = $rules->only(['name', 'description', 'moderators']);
                break;
            case "reorder":
                $rules = $rules->only('order');
                break;
        }

        return $rules->toArray();
    }

    public function create($data)
    {
        return Channel::create([
            'name' => $data['name'],
            'description' => $data['description']
        ]);
    }

    public function update($channel, $data)
    {
        return $channel->update([
            'name' => $data['name'],
            'description' => $data['description']
        ]);
    }

    public function assignModerators($channel, $users)
    {
        if(!$users || !count($users)) {
            $channel->moderators()->sync([]);
        }else{
            $users = User::whereIn('username', $users)->get();
            $channel->moderators()->sync($users);
        }
    }

    public function reorder($order)
    {
        // TODO -- find a better way to do this. sqlite honors the array order but mysql does not. so a special query
        //          is required for mysql.
        if(app()->environment() !== 'testing') {
            $order = implode("','", $order);
            $channels = Channel::whereIn('slug', $order)
                ->orderByRaw(DB::raw("FIELD(slug, '$order')"))
                ->get();
        }else{
            $channels = Channel::whereIn('slug', $order)->get();
        }

        Channel::setNewOrder($channels->pluck('id')->toArray());
    }

    public function viewed($channel)
    {
        $service = new ThreadsService();
        $threads = $channel->threads()->get();
        $user = auth()->user();

        foreach($threads as $thread) {
            $service->viewed($thread, $user);
        }
    }

    public function hasNewReplies($channel)
    {
        if(!auth()->check()) return false;

        $view = $channel->viewedBy()->where('users.id', auth()->user()->id)->get();

        if($view->count() === 0 || $view->count() !== $channel->threads()->count()) return true;

        $result = $view->search(function($item) {
            return $item->viewed_thread->timestamp < $item->viewed_thread->thread->latest_reply_at;
        });

        return $result !== false;
    }
}
