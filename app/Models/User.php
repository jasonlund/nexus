<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use Cog\Contracts\Ban\Bannable as BannableContract;
use Cog\Laravel\Ban\Traits\Bannable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmail;
use App\Events\UserDeleted;

class User extends Authenticatable implements BannableContract, JWTSubject, MustVerifyEmail
{
    use Notifiable, SoftDeletes, SoftCascadeTrait, HasRolesAndAbilities, Bannable;

    /**
     * The attributes that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'email', 'signature', 'password', 'avatar_path', 'location', 'timezone', 'last_active_at'
    ];

    /**
     * The attributes that should be hidden from arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'email_verified_at', 'last_active_at'
    ];

    /**
     * Cascade soft deletes to related Models.
     *
     * @var array
     */
    protected $softCascade = [
        'threads', 'replies'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return  mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return  array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Send the password reset notification.
     *
     * @param   string  $token
     *
     * @return  void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get the route key for the model.
     *
     * @return  string
     */
    public function getRouteKeyName()
    {
        return 'username';
    }

    /**
     * A User owns many Emotes.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function emotes()
    {
        return $this->hasMany('App\Models\Emote');
    }

    /**
     * A User owns many Threads.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function threads()
    {
        return $this->hasMany('App\Models\Thread');
    }

    /**
     * A User owns many Replies.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany('App\Models\Reply');
    }

    /**
     * A User belongs to many Channels as a Moderator or VIP.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function channels()
    {
        return $this->belongsToMany('App\Models\Channel')
            ->withPivot('ability_id');
    }

    /**
     * A User belongs to many Channels as a Moderator.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function moderatedChannels()
    {
        return $this->belongsToMany(
            'App\Models\Channel',
            'channel_moderator',
            'user_id',
            'channel_id'
        );
    }

    /**
     * A User belongs to many Threads through ViewedThread.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function viewed()
    {
        return $this->belongsToMany(
            'App\Models\Thread',
            'viewed_threads',
            'user_id',
            'thread_id'
        )
            ->using('App\Models\ViewedThread')
            ->withPivot('timestamp');
    }

    /**
     * Get the User's role. If none exists, return base 'user' role.
     *
     * @return  string
     */
    public function getRoleAttribute()
    {
        $role = $this->roles()->first();

        return $role ? $role->name : 'user';
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }
}
