<?php

namespace App\Models;

use Znck\Eloquent\Traits\BelongsToThrough;

class Reply extends Model
{
    use BelongsToThrough;

    /**
     * The attributes that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = [
        'body', 'user_id', 'edited_at', 'edited_by'
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
     * Replies belong to one owner.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * Replies optionally belong to one editor
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function editor()
    {
        return $this->belongsTo('App\Models\User', 'edited_by');
    }

    /**
     * Replies belong to one Thread.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thread()
    {
        return $this->belongsTo('App\Models\Thread');
    }

    /**
     * Replies belong to one Channel through one Thread.
     *
     * @return  \Znck\Eloquent\Relations\BelongsToThrough
     */
    public function channel()
    {
        return $this->belongsToThrough('App\Models\Channel', 'App\Models\Thread');
    }
}
