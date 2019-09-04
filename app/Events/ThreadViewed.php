<?php

namespace App\Events;

use App\Models\Thread;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ThreadViewed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The Thread which triggered the event.
     *
     * @var Thread
     */
    public $thread;

    /**
     * The user which triggered the event.
     *
     * @var User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param   Thread  $thread
     * @param   User    $user
     *
     * @return  void
     */
    public function __construct(Thread $thread, User $user)
    {
        $this->thread = $thread;
        $this->user = $user;
    }
}
