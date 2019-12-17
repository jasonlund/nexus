<?php

namespace App\Http\Controllers;

use App\Http\Requests\Thread\ThreadCreateRequest;
use App\Http\Requests\Thread\ThreadDestroyRequest;
use App\Http\Requests\Thread\ThreadLockRequest;
use App\Http\Requests\Thread\ThreadUpdateRequest;
use App\Http\Requests\Thread\ThreadPinRequest;
use App\Models\ChannelCategory;
use App\Models\Channel;
use App\Models\Thread;

class ThreadsController extends Controller
{
    /**
     * ThreadsController constructor.
     *
     * @return  void
     */
    public function __construct()
    {
        $this->middleware('limit.actions')->only(['store']);
        parent::__construct();
    }

    /**
     * Display a listing of the Threads in a Channel.
     *
     * @param   Channel          $channel
     * @param   ChannelCategory  $category
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function index(ChannelCategory $category, Channel $channel)
    {
        return paginated_response(
            $channel->threads()->orderBy('pinned', 'DESC')
            ->orderBy('updated_at', 'DESC'),
            'ThreadTransformer',
            ['owner', 'editor']
        );
    }

    /**
     * Store a newly created Thread in storage.
     *
     * @param   ThreadCreateRequest  $request
     * @param   ChannelCategory      $category
     * @param   Channel              $channel
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function store(ThreadCreateRequest $request, ChannelCategory $category, Channel $channel)
    {
        $thread = $this->service->create($channel, $request->all());

        return item_response(
            $thread,
            'ThreadTransformer',
            ['owner', 'latest_reply', 'latest_reply.owner', 'editor']
        );
    }

    /**
     * Display the specified Thread.
     *
     * @param   ChannelCategory  $category
     * @param   Channel          $channel
     * @param   Thread           $thread
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function show(ChannelCategory $category, Channel $channel, Thread $thread)
    {
        $this->service->show($thread);

        return item_response(
            $thread,
            'ThreadTransformer',
            ['owner', 'editor']
        );
    }

    /**
     * Update the specified Thread in storage.
     *
     * @param   ThreadUpdateRequest  $request
     * @param   ChannelCategory      $category
     * @param   Channel              $channel
     * @param   Thread               $thread
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ThreadUpdateRequest $request, ChannelCategory $category, Channel $channel, Thread $thread)
    {
        $this->service->update($thread, $request->all());

        return item_response(
            $thread,
            'ThreadTransformer',
            ['owner', 'latest_reply', 'latest_reply.owner', 'editor']
        );
    }

    /**
     * Remove the specified Thread from storage.
     *
     * @param   ThreadDestroyRequest  $request
     * @param   ChannelCategory       $category
     * @param   Channel               $channel
     * @param   Thread                $thread
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function destroy(ThreadDestroyRequest $request, ChannelCategory $category, Channel $channel, Thread $thread)
    {
        $thread->delete();

        return response('', 204);
    }

    /**
     * Toggle the Lock status of the given Thread.
     *
     * @param   ThreadLockRequest  $request
     * @param   ChannelCategory    $category
     * @param   Channel            $channel
     * @param   Thread             $thread
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function lock(ThreadLockRequest $request, ChannelCategory $category, Channel $channel, Thread $thread)
    {
        $this->service->toggleLock($thread);

        return item_response(
            $thread,
            'ThreadTransformer',
            ['owner', 'latest_reply', 'latest_reply.owner', 'editor']
        );
    }

    /**
     * Toggle the Pin status of the given Thread.
     *
     * @param   ThreadPinRequest   $request
     * @param   ChannelCategory    $category
     * @param   Channel            $channel
     * @param   Thread             $thread
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function pin(ThreadPinRequest $request, ChannelCategory $category, Channel $channel, Thread $thread)
    {
        $this->service->togglePin($thread);

        return item_response(
            $thread,
            'ThreadTransformer',
            ['owner', 'latest_reply', 'latest_reply.owner', 'editor']
        );
    }
}
