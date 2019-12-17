<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\User;
use Bouncer;
use Storage;
use App\Services\PurifyService;

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
            'username' => (string) $user->username,
            'role' => $user->role,
            'avatar' => $user->avatar_path ? Storage::url($user->avatar_path) : null,
        ];

        $data['moderatable_channels'] = $data['role'] !== 'moderator' ? []
            : $user->moderatedChannels->sortBy('slug')->pluck('slug');

        return $data;
    }
}
