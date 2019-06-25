<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\User;
use Bouncer;
use Storage;
use App\Services\PurifyService;

class UserTransformer extends TransformerAbstract
{
    /**
     * Transform Users.
     *
     * @param \App\Models\User $user
     * @return array
     */
    public function transform(User $user)
    {
        $data = [
            'username' => (string) $user->username,
            'name' => (string) $user->name,
            'role' => $user->role,
            'avatar' => $user->avatar_path ? url(Storage::url($user->avatar_path)) : null,
            'signature' => $user->signature ? (string) PurifyService::simple($user->signature) : null,
            'thread_count' => (int) $user->threads()->count(),
            'reply_count' => (int) $user->replies()->count(),
            'timezone' => (string) $user->timezone,
            'location' => $user->location ? (string) $user->location : null,
            'created_at' => (string) $user->created_at,
            'updated_at' => (string) $user->updated_at,
            'last_active_at' => (string) $user->last_active_at->format('Y-m-d H:i:s')
        ];

        $data['moderatable_channels'] = $data['role'] !== 'moderator' ? [] :
            $user->moderatedChannels()->pluck('slug');

        /**
         * If the User is the currently authenticated User or the currently authenticated User has the ability to
         * view other users include the email.
         */
        if((auth()->check() && auth()->user()->id === $user->id
                && strpos(request()->route()->getName(), 'self.') !== false)
            || Bouncer::can('view-all-users')){
            $data['email'] = (string) $user->email;
        }

        /**
         * If the currently authenticated User has the ability to view other users include the User's ban status.
         */
        if(Bouncer::can('view-all-users'))
        {
            $data['banned'] = (bool) $user->isBanned();
            $data['banned_at'] = (string) $user->banned_at;
            if($data['banned']){
                $data['banned_until'] = $user->bans->first()->expired_at ? (string) $user->bans->first()->expired_at->format('Y-m-d H:i:s') : null;
                $data['ban_comment'] = $user->bans->first()->comment ? (string) $user->bans->first()->comment : null;
            }else{
                $data['banned_until'] = null;
                $data['ban_comment'] = null;
            }
        }

        return $data;
    }
}
