<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\User;
use Bouncer;
use Storage;
use App\Services\PurifyService;
use Cache;

class UserTransformer extends TransformerAbstract
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
            'username' => (string) $user->username,
            'name' => (string) $user->name,
            'role' => $user->role,
            'avatar' => $user->avatar_path ? Storage::url($user->avatar_path) : null,
            'signature' => $user->signature ? (string) PurifyService::simple($user->signature) : null,
            'thread_count' => (int) Cache::rememberForever('user-thread-count-' . $user->id, function () use ($user) {
                return $user->threads()->count();
            }),
            'reply_count' => (int) Cache::rememberForever('user-reply-count-' . $user->id, function () use ($user) {
                return $user->replies()->count();
            }),
            'timezone' => (string) $user->timezone,
            'location' => $user->location ? (string) $user->location : null,
            'created_at' => (string) $user->created_at,
            'updated_at' => (string) $user->updated_at,
            'last_active_at' => (string) $user->last_active_at->format('Y-m-d H:i:s'),
            // 'verified' => (boolean) $user->hasVerifiedEmail()
            'verified' => true
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

        /**
         * If the currently authenticated User has the ability to ban users include the User's ban status.
         */
        if (Bouncer::can('ban-users')) {
            $data['banned'] = (bool) $user->isBanned();
            $data['banned_at'] = (string) $user->banned_at;
            if ($data['banned']) {
                $data['banned_until'] = $user->bans->first()->expired_at ?
                    (string) $user->bans->first()->expired_at->format('Y-m-d H:i:s') : null;
                $data['ban_comment'] = $user->bans->first()->comment ? (string) $user->bans->first()->comment : null;
            } else {
                $data['banned_until'] = null;
                $data['ban_comment'] = null;
            }
        }

        return $data;
    }
}
