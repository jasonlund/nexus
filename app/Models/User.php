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
use Tymon\JWTAuth\Contracts\JWTSubject;
use Hash;

class User extends Authenticatable implements BannableContract, JWTSubject
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

    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [];
    }

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

    public static function validationRules($key = null)
    {
        $ignoreUsername = $ignoreEmail = null;

        if(request()->route('user')){
            $ignoreUsername = request()->route('user')->username;
            $ignoreEmail = request()->route('user')->email;
        }else if(auth()->check()) {
            $ignoreUsername = auth()->user()->username;
            $ignoreEmail = auth()->user()->email;
        }
        $rules = [
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'min:3',
                'max:16',
                'alpha_dash',
                new UniqueCaseInsensitive(self::class, $ignoreUsername)
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($ignoreEmail, 'email')
            ],
            'password' => [
                'string',
                'min:8',
                'confirmed',
                'case_diff',
                'numbers',
                'letters'
            ]
        ];

        return $key ? $rules[$key] : $rules;
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        if(isset($attributes['password'])){
            $attributes['password'] = Hash::make($attributes['password']);
        }

        return parent::update($attributes, $options);
    }
}
