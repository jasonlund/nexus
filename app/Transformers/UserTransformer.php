<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\User;

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

        if(app()->environment() === 'testing') $data['id'] = $user->id;

        return $data;
    }
}
