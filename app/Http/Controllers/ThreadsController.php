<?php

namespace App\Http\Controllers;

use App\Http\Requests\Thread\ThreadCreateRequest;
use App\Http\Requests\Thread\ThreadDestroyRequest;
use App\Http\Requests\Thread\ThreadLockRequest;
use App\Http\Requests\Thread\ThreadUpdateRequest;
use App\Models\Channel;
use App\Models\Thread;
use App\Services\ThreadsService;

class ThreadsController extends Controller
{
    /**
     * The Threads Service
     *
     * @var ThreadsService
     */
//    protected $service;

    /**
     * ThreadsController constructor.
     *
     * @param ThreadsService $service
     */
    public function __construct(ThreadsService $service)
    {
//        $this->service = $service;
        parent::__construct();
    }

    /**
     * Display a listing of the Threads in a Channel.
     *
     * @param \App\Models\Channel $channel
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Channel $channel)
    {
        return paginated_response($channel->threads()->orderBy('updated_at', 'DESC'),
            'ThreadTransformer', ['owner', 'latest_reply', 'latest_reply.owner', 'editor']);
    }

    /**
     * Store a newly created Thread in storage.
     *
     * @param \App\Http\Requests\ThreadCreateRequest
     * @param \App\Models\Channel $channel
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ThreadCreateRequest $request, Channel $channel)
    {
        $thread = $this->service->create($channel, request()->all());

        return item_response($thread, 'ThreadTransformer', ['owner', 'latest_reply', 'latest_reply.owner', 'editor']);
    }

    /**
     * Display the specified Thread.
     *
     * @param \App\Models\Channel $channel
     * @param \App\Models\Thread $thread
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Channel $channel, Thread $thread)
    {
        $this->service->show($thread);

        return item_response($thread, 'ThreadTransformer', ['owner', 'latest_reply', 'latest_reply.owner', 'editor']);
    }

    /**
     * Update the specified Thread in storage.
     *
     * @param \App\Http\Requests\ThreadUpdateRequest
     * @param \App\Models\Channel $channel
     * @param \App\Models\Thread $thread
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ThreadUpdateRequest $request, Channel $channel, Thread $thread)
    {
        $this->service->update($thread, request()->all());

        return item_response($thread, 'ThreadTransformer', ['owner', 'latest_reply', 'latest_reply.owner', 'editor']);
    }

    /**
     * Remove the specified Thread from storage.
     *
     * @param \App\Http\Requests\ThreadDestroyRequest
     * @param \App\Models\Channel $channel
     * @param \App\Models\Thread
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(ThreadDestroyRequest $request, Channel $channel, Thread $thread)
    {
        $thread->delete();

        return response()->json();
    }

    public function lock(ThreadLockRequest $request, Channel $channel, Thread $thread)
    {
        $this->service->toggleLock($thread);

        return item_response($thread, 'ThreadTransformer', ['owner', 'latest_reply', 'latest_reply.owner', 'editor']);
    }
}
