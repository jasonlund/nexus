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
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Cache;
use Carbon\Carbon;

class User extends Authenticatable implements BannableContract, JWTSubject
{
    use Notifiable, SoftDeletes, HasRolesAndAbilities, Bannable;

    /**
     * The attributes that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = ['name', 'username', 'email', 'signature', 'password', 'avatar_path', 'location', 'timezone'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Cast Soft Deletes timestamp as a date.
     *
     * @var array
     */
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
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
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

    /**
     * A User owns many Threads.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function threads()
    {
        return $this->hasMany('App\Models\Thread');
    }

    /**
     * A User owns many Replies.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany('App\Models\Reply');
    }

    /**
     * A User belongs to many Channels as a Moderator or VIP.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function channels()
    {
        return $this->belongsToMany('App\Models\Channel')
            ->withPivot('ability_id');
    }

    /**
     * A Users belongs to many Channels as a Moderator
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function moderatedChannels()
    {
        return $this->belongsToMany('App\Models\Channel',
            'channel_moderator',
            'user_id',
            'channel_id');
    }

    public function viewed()
    {
        return $this->belongsToMany('App\Models\Thread', 'viewed_threads', 'user_id', 'thread_id')
            ->using('App\Models\ViewedThread')
            ->withPivot('timestamp');
    }
}
