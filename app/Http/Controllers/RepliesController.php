<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reply\ReplyCreateRequest;
use App\Http\Requests\Reply\ReplyDestroyRequest;
use App\Http\Requests\Reply\ReplyUpdateRequest;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\Channel;
use Illuminate\Http\Request;
use App\Transformers\ReplyTransformer;

class RepliesController extends Controller
{
    /**
     * Display a paginated listing of a thread's replies in order.
     *
     * @param Channel $channel
     * @param Thread $thread
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Channel $channel, Thread $thread)
    {
        $data = $thread->replies();

        return paginated_response($data, 'ReplyTransformer', ['owner']);
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
        $reply = $thread->addReply([
            'body' => request('body'),
            'user_id' => auth()->user()->id
        ]);

        return response()->json(fractal()
            ->item($reply)
            ->includeOwner()
            ->transformWith(new ReplyTransformer()));
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
        $reply->update([
            'body' => request('body')
        ]);

        return response()->json(fractal()
            ->item($reply)
            ->includeOwner()
            ->transformWith(new ReplyTransformer()));
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
