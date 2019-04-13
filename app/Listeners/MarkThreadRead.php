<?php

namespace App\Listeners;

use App\Events\ThreadViewed;
use App\Services\ThreadsService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class MarkThreadRead
{
    protected $service;

    public function __construct(ThreadsService $service)
    {
        $this->service = $service;
    }

    /**
     * Handle the event.
     *
     * @param  ThreadViewed  $event
     * @return void
     */
    public function handle(ThreadViewed $event)
    {
        $this->service->viewed($event->thread, $event->user);
    }
}
