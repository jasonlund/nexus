<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reply\ReplyCreateRequest;
use App\Http\Requests\Reply\ReplyDestroyRequest;
use App\Http\Requests\Reply\ReplyUpdateRequest;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\Channel;
use App\Services\RepliesService;
use Illuminate\Http\Request;
use App\Transformers\ReplyTransformer;

class RepliesController extends Controller
{
    protected $service;

    public function __construct(RepliesService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a paginated listing of a thread's replies in order.
     *
     * @param Channel $channel
     * @param Thread $thread
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Channel $channel, Thread $thread)
    {
        return paginated_response($thread->replies(), 'ReplyTransformer', ['owner', 'editor']);
    }

    /**
     * Store a newly created Reply in storage.
     *
     * @param  \App\Http\Requests\ReplyCreateRequest  $request
     * @param  \App\Models\Channel $channel
     * @param  \App\Models\Thread $thread
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ReplyCreateRequest $request, Channel $channel, Thread $thread)
    {
        $reply = $this->service->create($thread, request()->all());

        return item_response($reply, 'ReplyTransformer', ['owner', 'editor']);
    }

    /**
     * Update the specified Reply in storage.
     *
     * @param  \App\Http\Requests\ReplyUpdateRequest $request
     * @param  \App\Models\Channel $channel
     * @param  \App\Models\Reply  $reply
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ReplyUpdateRequest $request, Channel $channel, Thread $thread, Reply $reply)
    {
        $this->service->update($reply, request()->all());

        return item_response($reply, 'ReplyTransformer', ['owner', 'editor']);
    }

    /**
     * Remove the specified Reply from storage.
     *
     * @param \App\Http\Requests\ReplyDestroyRequest $request
     * @param  \App\Models\Channel $channel
     * @param  \App\Models\Reply  $reply
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(ReplyDestroyRequest $request, Channel $channel, Thread $thread, Reply $reply)
    {
        $reply->delete();

        return response()->json();
    }
}
