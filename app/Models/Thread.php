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
    protected $fillable = [
        'title', 'body', 'user_id', 'locked', 'edited_at', 'edited_by'
    ];

    /**
     * Cascade deletes to related Replies
     *
     * @var array
     */
    protected $softCascade = [
        'replies'
    ];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = [
        'channel'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'edited_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'locked' => 'boolean',
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return  array
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
     * @param   Builder  $query
     * @param   Model    $model
     *
     * @return  Builder
     */
    public function scopeWithUniqueSlugConstraints(Builder $query, Model $model)
    {
        return $query->where('channel_id', $model->channel_id);
    }

    /**
     * Scope Thread slug route binding to the Channel it belongs to.
     *
     * @param   string  $value
     *
     * @return  self
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany('App\Models\Reply');
    }

    /**
     * Threads optionally have a latest reply.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestReply()
    {
        return $this->hasOne('App\Models\Reply', 'id', 'latest_reply_id');
    }

    /**
     * Threads belong to one Owner.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * Threads optionally belong to one Editor.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function editor()
    {
        return $this->belongsTo('App\Models\User', 'edited_by');
    }

    /**
     * Threads belong to one Channel.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo('App\Models\Channel');
    }

    /**
     * Threads have many views.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function views()
    {
        return $this->hasMany('App\Models\ViewedThread');
    }

    /**
     * Threads belong to many viewers (Users) through ViewedThread.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function viewedBy()
    {
        return $this->belongsToMany(
            'App\Models\User',
            'viewed_threads',
            'thread_id',
            'user_id'
        )
            ->using('App\Models\ViewedThread')
            ->withPivot('timestamp');
    }
}
