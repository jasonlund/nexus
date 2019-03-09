<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReplyCreateRequest;
use App\Http\Requests\ReplyDestroyRequest;
use App\Http\Requests\ReplyUpdateRequest;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\Channel;
use Illuminate\Http\Request;
use App\Transformers\ReplyTransformer;

class RepliesController extends Controller
{
    /**
     * RepliesController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['index']);
    }

    public function index(Channel $channel, Thread $thread)
    {
        $data = $thread->replies();

        return paginated_response($data, 'ReplyTransformer', ['owner']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ReplyCreateRequest  $request
     * @param  \App\Models\Channel $channel
     * @param  \App\Models\Thread $thread
     * @return \Illuminate\Http\Response
     */
    public function store(ReplyCreateRequest $request, Channel $channel, Thread $thread)
    {
        $reply = $thread->addReply([
            'body' => request('body'),
            'user_id' => auth()->user()->id
        ]);

        return response()->json(fractal()
            ->item($reply)
            ->transformWith(new ReplyTransformer()));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function edit(Reply $reply)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\ReplyUpdateRequest $request
     * @param  \App\Models\Channel $channel
     * @param  \App\Models\Reply  $reply
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function update(ReplyUpdateRequest $request, Channel $channel, Thread $thread, Reply $reply)
    {
        $reply->update([
            'body' => request('body')
        ]);

        return response()->json(fractal()
            ->item($reply)
            ->transformWith(new ReplyTransformer()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Http\Requests\ReplyDestroyRequest $request
     * @param  \App\Models\Channel $channel
     * @param  \App\Models\Reply  $reply
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(ReplyDestroyRequest $request, Channel $channel, Thread $thread, Reply $reply)
    {
        $reply->delete();

        return response()->json();
    }
}
