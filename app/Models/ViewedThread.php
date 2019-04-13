<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Builder;

class ViewedThread extends Pivot
{
    protected $table = 'viewed_threads';

    protected $fillable = ['user_id', 'thread_id', 'timestamp'];

    protected $dates = ['timestamp'];

    public $timestamps = false;

    protected function setKeysForSaveQuery(Builder $query)
    {
        $query
            ->where('user_id', '=', $this->getAttribute('user_id'))
            ->where('thread_id', '=', $this->getAttribute('thread_id'));
        return $query;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function thread()
    {
        return $this->belongsTo('App\Models\Thread');
    }
}
