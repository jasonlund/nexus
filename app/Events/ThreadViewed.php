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

    public $thread;
    public $user;

    /**
     * @param Thread $thread
     * @param User $user
     */
    public function __construct(Thread $thread, User $user)
    {
        $this->thread = $thread;
        $this->user = $user;
    }
}
