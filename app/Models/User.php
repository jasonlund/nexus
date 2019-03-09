<?php

namespace App\Models;

use App\Rules\UniqueCaseInsensitive;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use Bouncer;
use App\Models\Channel;
use Illuminate\Validation\Rule;
use Cog\Contracts\Ban\Bannable as BannableContract;
use Cog\Laravel\Ban\Traits\Bannable;

class User extends Authenticatable implements BannableContract
{
    use Notifiable, SoftDeletes, HasRolesAndAbilities, Bannable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'username';
    }

    public function threads()
    {
        return $this->hasMany('App\Models\Thread');
    }

    public function replies()
    {
        return $this->hasMany('App\Models\Reply');
    }

    public function channels()
    {
        return $this->belongsToMany('App\Models\Channel')
            ->withPivot('ability_id');
    }

    public function moderatedChannels()
    {
        return $this->belongsToMany('App\Models\Channel',
            'channel_moderator',
            'channel_id',
            'user_id');
    }

    public static function validationRules()
    {
        return [
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'min:3',
                'max:16',
                'alpha_dash',
                new UniqueCaseInsensitive(self::class, request()->route('user') ?
                    request()->route('user')->username :
                    auth()->user()->username)
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore(request()->route('user') ?
                    request()->route('user')->email :
                    auth()->user()->email, 'email')
            ]
        ];
    }
}
