<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Bouncer;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Channel extends Model implements Sortable
{
    use Sluggable, SoftCascadeTrait, SortableTrait;

    /**
     * The attributes that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = ['name', 'description'];

    /**
     * Cascade deletes to related Threads.
     *
     * @var array
     */
    protected $softCascade = ['threads'];

    /**
     * Allow Channels to be ordered.
     *
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

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

    /**
     * A Channel has many Threads.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function threads()
    {
        return $this->hasMany('App\Models\Thread');
    }

    /**
     * A Channel has many Replies through Threads.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function replies()
    {
        return $this->hasManyThrough('App\Models\Reply', 'App\Models\Thread');
    }

    /**
     * A Channel has many Moderators (Users).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function moderators()
    {
        return $this->belongsToMany('App\Models\User',
            'channel_moderator',
            'user_id',
            'channel_id');
    }

    /**
     * Add a Thread to this Channel.
     *
     * @param $attributes
     * @return \App\Models\Thread
     */
    public function addThread($attributes)
    {
        return $this->threads()->create($attributes);
    }
}
