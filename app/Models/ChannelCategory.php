<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class ChannelCategory extends Model implements Sortable
{
    use Sluggable, SoftCascadeTrait, SortableTrait;

    /**
     * The attributes that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'order' => 'integer'
    ];

    /**
     * Cascade deletes to related Channels.
     *
     * @var array
     */
    protected $softCascade = [
        'channels'
    ];

    /**
     * Allow Channel Categories to be ordered.
     *
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
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
                'source' => 'name'
            ]
        ];
    }

    /**
     * Get the route key for the model.
     *
     * @return  string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * A Channel Category has many Channels.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function channels()
    {
        return $this->hasMany('App\Models\Channel');
    }
}
