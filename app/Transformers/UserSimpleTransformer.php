<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\User;
use Bouncer;
use Storage;
use App\Services\PurifyService;
use Cache;

class UserSimpleTransformer extends TransformerAbstract
{
    /**
     * Transform Users.
     *
     * @param   User  $user
     *
     * @return  array
     */
    public function transform(User $user)
    {
        $data = [
            'name' => (string) $user->name,
            'username' => (string) $user->username,
            'role' => $user->role,
            'avatar' => $user->avatar_path ? Storage::url($user->avatar_path) : null,
            'signature' => $user->signature ? (string) PurifyService::simple($user->signature) : null,
            'thread_count' => (int) Cache::rememberForever('user-thread-count-' . $user->id, function () use ($user) {
                return $user->threads()->count();
            }),
            'reply_count' => (int) Cache::rememberForever('user-reply-count-' . $user->id, function () use ($user) {
                return $user->replies()->count();
            }),
        ];

        $data['moderatable_channels'] = $data['role'] !== 'moderator' ? []
            : $user->moderatedChannels->sortBy('slug')->pluck('slug');

        /**
         * If the User is the currently authenticated User or the currently authenticated User has the ability to
         * view other users include the email.
         */
        if ((auth()->check() && auth()->user()->id === $user->id
                && strpos(request()->route()->getName(), 'self.') !== false)
            || Bouncer::can('view-all-users')
        ) {
            $data['email'] = (string) $user->email;
        }

        return $data;
    }
}
