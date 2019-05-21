<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Bouncer;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Channel extends Model implements Sortable
{
    use Sluggable, SoftCascadeTrait, SortableTrait, HasRelationships;

    /**
     * The attributes that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'locked'];

    protected $casts = [
        'locked' => 'boolean'
    ];

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
     * Fill gaps in sort order on delete.
     */
    protected static function boot() {
        parent::boot();
        static::deleted(function() {
            self::setNewOrder(self::ordered()->pluck('id')->toArray());
        });
    }

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
            'channel_id',
            'user_id');
    }

    public function viewedBy()
    {
        return $this->hasManyDeep(
            'App\Models\User', ['App\Models\Thread', 'App\Models\ViewedThread'])
            ->withIntermediate('App\Models\ViewedThread');
    }
}
