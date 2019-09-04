<?php

namespace App\Events;

use App\Models\Reply;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReplyCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The Reply which triggered the event.
     *
     * @var Reply
     */
    public $reply;

    /**
     * Create a new event instance.
     *
     * @param   Reply  $reply
     *
     * @return void
     */
    public function __construct(Reply $reply)
    {
        $this->reply = $reply;
    }
}
