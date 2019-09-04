<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Builder;

class ViewedThread extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'viewed_threads';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'thread_id', 'timestamp'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'timestamp'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Set the keys for a save update query.
     *
     * @param   Builder  $query
     *
     * @return  Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $query->where('user_id', '=', $this->getAttribute('user_id'))
            ->where('thread_id', '=', $this->getAttribute('thread_id'));
        return $query;
    }

    /**
     * A ViewedThread belongs to one User.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * A ViewedThread belongs to one Thread.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thread()
    {
        return $this->belongsTo('App\Models\Thread');
    }
}
