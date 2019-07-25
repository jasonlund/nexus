<?php

namespace App\Events;

use App\Models\Reply;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReplyDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reply;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Reply $reply)
    {
        $this->reply = $reply;
    }
}
