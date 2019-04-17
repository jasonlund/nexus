<?php

namespace App\Models;
use App\Events\ReplyDeleted;
use Znck\Eloquent\Traits\BelongsToThrough;
use App\Events\ReplyCreated;

class Reply extends Model
{
    use BelongsToThrough;

    /**
     * The attributes that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = ['body', 'user_id'];

    protected $dispatchesEvents = [
        'created' => ReplyCreated::class,
        'deleted' => ReplyDeleted::class
    ];

    /**
     * Replies belong to one owner.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * Replies belong to one Thread.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thread()
    {
        return $this->belongsTo('App\Models\Thread');
    }

    /**
     * Replies belong to one Channel through one Thread.
     *
     * @return \Znck\Eloquent\Relations\BelongsToThrough
     */
    public function channel()
    {
        return $this->belongsToThrough('App\Models\Channel', 'App\Models\Thread');
    }
}
