<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class Channel extends Model
{
    use Sluggable, SoftCascadeTrait;

    protected $softCascade = ['threads'];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
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
