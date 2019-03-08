<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\User;
use Bouncer;

class UserTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param \App\Models\User $user
     * @return array
     */
    public function transform(User $user)
    {
        $data = [
            'username' => $user->username,
            'name' => $user->name,
        ];

        if((auth()->check() && auth()->user()->id === $user->id) || Bouncer::can('view-all-users')){
            $data['email'] = $user->email;
        }

        if(Bouncer::can('view-all-users'))
        {
            $data['banned'] = $user->isBanned();
            $data['banned_at'] = $user->banned_at;
            if($data['banned']){
                $data['banned_until'] = $user->bans->first()->expired_at ? $user->bans->first()->expired_at->format('Y-m-d H:i:s') : null;
                $data['ban_comment'] = $user->bans->first()->comment;
            }else{
                $data['banned_until'] = null;
                $data['ban_comment'] = null;
            }
        }

        return $data;
    }
}
