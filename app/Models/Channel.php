<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Illuminate\Database\Eloquent\Builder;

class Channel extends Model implements Sortable
{
    use Sluggable, SoftCascadeTrait, SortableTrait, HasRelationships;

    /**
     * The attributes that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'channel_category_id', 'image', 'locked'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'locked' => 'boolean',
        'order' => 'integer'
    ];

    /**
     * Cascade deletes to related Threads.
     *
     * @var array
     */
    protected $softCascade = [
        'threads'
    ];

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
     * Channels slugs are unique to which ChannelCategory it belongs to.
     *
     * @param   Builder  $query
     * @param   Model    $model
     *
     * @return  Builder
     */
    public function buildSortQuery()
    {
        return static::query()
            ->where('channel_category_id', $this->channel_category_id);
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return  array
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
     * Thread slugs are unique to which Channel it belongs to.
     *
     * @param   Builder  $query
     * @param   Model    $model
     *
     * @return  Builder
     */
    public function scopeWithUniqueSlugConstraints(Builder $query, Model $model)
    {
        return $query->where('channel_category_id', $model->channel_category_id);
    }

    /**
     * Scope Channel slug route binding to the ChannelCategory it belongs to.
     *
     * @param   string  $value
     *
     * @return  self
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function resolveRouteBinding($value)
    {
        return $this->where('channel_category_id', request()->route('category')->id)
            ->where('slug', $value)
            ->first() ?? abort(404);
    }

    /**
     * A Channel belongs to one Channel Category
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\ChannelCategory', 'channel_category_id');
    }

    /**
     * A Channel has many Threads.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function threads()
    {
        return $this->hasMany('App\Models\Thread');
    }

    /**
     * A Channel has many Replies through Threads.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function replies()
    {
        return $this->hasManyThrough('App\Models\Reply', 'App\Models\Thread');
    }

    /**
     * A Channel belongs to many Moderators (Users).
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function moderators()
    {
        return $this->belongsToMany(
            'App\Models\User',
            'channel_moderator',
            'channel_id',
            'user_id'
        );
    }

    /**
     * A Channel has many views (Users) through ViewedThread
     *
     * @return  \Staudenmeir\EloquentHasManyDeep\HasManyDeep
     */
    public function viewedBy()
    {
        return $this->hasManyDeep(
            'App\Models\User',
            ['App\Models\Thread', 'App\Models\ViewedThread']
        )->withIntermediate('App\Models\ViewedThread');
    }
}
