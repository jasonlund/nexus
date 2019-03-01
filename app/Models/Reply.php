<?php

namespace App\Models;
use Znck\Eloquent\Traits\BelongsToThrough;

class Reply extends Model
{
    use BelongsToThrough;

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
    public function thread()
    {
        return $this->belongsTo('App\Models\Thread');
    }

    public function channel()
    {
        return $this->belongsToThrough('App\Models\Channel', 'App\Models\Thread');
    }
}
