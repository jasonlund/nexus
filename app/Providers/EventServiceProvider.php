<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Illuminate\Auth\Events\Registered' => [
            'Illuminate\Auth\Listeners\SendEmailVerificationNotification',
        ],
        'App\Events\ThreadViewed' => [
            'App\Listeners\MarkThreadRead',
        ],
        'App\Events\ReplyCreated' => [
            'App\Listeners\UpdateThreadReplyColumns',
        ],
        'App\Events\ReplyDeleted' => [
            'App\Listeners\UpdateThreadReplyColumns',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
