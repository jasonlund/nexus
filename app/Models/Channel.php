<?php

namespace App\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Channel extends Model
{
    use HasSlug;

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(50);
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function threads()
    {
        return $this->hasMany('App\Models\Thread');
    }

    public function replies()
    {
        return $this->hasManyThrough('App\Models\Reply', 'App\Models\Thread');
    }

    /**
     * @param $attributes
     * @return \App\Models\Thread
     */
    public function addThread($attributes)
    {
        return $this->threads()->create($attributes);
    }
}
