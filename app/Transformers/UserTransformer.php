<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\User;
use Bouncer;
use Storage;
use Purify;

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
        $role = $user->roles()->first();
        $data = [
            'username' => (string) $user->username,
            'name' => (string) $user->name,
            'role' => $role ? (string) $role->name : 'user',
            'avatar' => $user->avatar_path ? url(Storage::url($user->avatar_path)) : null,
            'signature' => $user->signature ? (string) Purify::clean($user->signature) : null,
            'timezone' => (string) $user->timezone
        ];

        $data['moderatable_channels'] = $data['role'] !== 'moderator' ? [] :
            $user->moderatedChannels()->pluck('slug');

        /**
         * If the User is the currently authenticated User or the currently authenticated User has the ability to
         * view other users include the email.
         */
        if((auth()->check() && auth()->user()->id === $user->id) || Bouncer::can('view-all-users')){
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
