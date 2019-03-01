<?php

namespace App\Models;

class Thread extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany('App\Models\Reply');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo('App\Models\Channel');
    }

    /**
     * @param $attributes
     * @return \App\Models\Thread
     */
    public function addReply($attributes)
    {
        return $this->replies()->create($attributes);
    }
}
