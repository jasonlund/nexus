<?php

namespace App\Events;

use App\Models\Thread;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ThreadDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The Thread which triggered the event.
     *
     * @var Thread
     */
    public $thread;

    /**
     * Create a new event instance.
     *
     * @param   Thread  $thread
     *
     * @return void
     */
    public function __construct(Thread $thread)
    {
        $this->thread = $thread;
    }
}
