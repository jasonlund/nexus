<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class Thread extends Model
{
    use Sluggable, SoftCascadeTrait;

    /**
     * The attributes that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = ['title', 'body', 'user_id'];

    protected $softCascade = ['replies'];

    protected $touches = ['channel'];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    /**
     * Thread slugs are unique to which Channel it belongs to.
     *
     * @param Builder $query
     * @param Model $model
     * @return Builder
     */
    public function scopeWithUniqueSlugConstraints(Builder $query, Model $model)
    {
        return $query->where('channel_id', $model->channel_id);
    }

    /**
     * Scope Thread slug route binding to the Channel it belows to.
     *
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value)
    {
        return $this->where('channel_id', request()->route('channel')->id)
                ->where('slug', $value)
                ->first() ?? abort(404);
    }

    /**
     * Threads have many Replies.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany('App\Models\Reply');
    }

    /**
     * Threads belong to one Owner.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * Threads belong to one Channel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo('App\Models\Channel');
    }

    /**
     * Add a Reply to this Thread.
     *
     * @param $attributes
     * @return \App\Models\Thread
     */
    public function addReply($attributes)
    {
        return $this->replies()->create($attributes);
    }
}
