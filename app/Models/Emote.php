<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Transformers\EmoteTransformer;

class Emote extends Model
{
    /**
     * The attributes that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'path', 'user_id'
    ];

    /**
     * Get the route key for the model.
     *
     * @return  string
     */
    public function getRouteKeyName()
    {
        return 'name';
    }

    /**
     * Emotes belong to one Owner.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * Get a properly transformed JSON response for all Emotes
     *
     * @return  string
     */
    public static function response()
    {
        return fractal()
            ->collection(self::orderBy('name')->get())
            ->transformWith(new EmoteTransformer())
            ->toJson();
    }
}
