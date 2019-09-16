<?php

namespace App\Transformers;

use App\Models\Emote;
use League\Fractal\TransformerAbstract;
use Storage;

class EmoteTransformer extends TransformerAbstract
{
    /**
     * The relationships that are available for inclusion
     *
     * @var array
     */
    protected $availableIncludes = [
        'owner'
    ];

    /**
     * Transform Emotes.
     *
     * @param   Emote  $emote
     *
     * @return  array
     */
    public function transform(Emote $emote)
    {
        $data = [
            'name' => (string) $emote->name,
            'url' => (string) Storage::url($emote->path),
            'owner' => (string) $emote->owner->username ?? ''
        ];

        return $data;
    }

    /**
     * Include the User (owner).
     *
     * @param   Emote  $emote
     *
     * @return  \League\Fractal\Resource\Item
     */
    public function includeOwner(Emote $emote)
    {
        return $this->item($emote->owner, new UserTransformer);
    }
}
