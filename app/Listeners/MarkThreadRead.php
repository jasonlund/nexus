<?php

namespace App\Listeners;

use App\Events\ThreadViewed;
use App\Services\ThreadsService;

class MarkThreadRead
{
    protected $service;

    /**
     * MarkThreadRead constructor.
     * Inject the service.
     *
     * @param   ThreadsService  $service
     *
     * @return  void
     */
    public function __construct(ThreadsService $service)
    {
        $this->service = $service;
    }

    /**
     * Mark the specified Thread as viewed by the authenticated User.
     *
     * @param   ThreadViewed  $event
     *
     * @return  void
     */
    public function handle(ThreadViewed $event)
    {
        $this->service->viewed($event->thread, $event->user);
    }
}
