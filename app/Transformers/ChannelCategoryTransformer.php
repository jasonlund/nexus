<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\ChannelCategory;

class ChannelCategoryTransformer extends TransformerAbstract
{
    /**
     * The relationships that are available for inclusion
     *
     * @var array
     */
    protected $availableIncludes = [
        'channels'
    ];

    /**
     * Transform a ChannelCategory
     *
     * @param   ChannelCategory  $category
     *
     * @return  array
     */
    public function transform(ChannelCategory $category)
    {
        $data = [
            'name' => (string) $category->name,
            'slug' => (string) $category->slug,
            'order' => (int) $category->order
        ];

        return $data;
    }

    /**
     * Include the Channels in the ChannelCategory if any exist.
     *
     * @param   ChannelCategory  $category
     *
     * @return  \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */
    public function includeChannels(ChannelCategory $category)
    {
        return $category->channels()->count() !== 0 ?
            $this->collection($category->channels()->ordered()->get(), new ChannelTransformer()) : $this->null();
    }
}
