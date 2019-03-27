<?php

namespace App\Http\Controllers;

use App\Http\Requests\Thread\ThreadCreateRequest;
use App\Http\Requests\Thread\ThreadDestroyRequest;
use App\Http\Requests\Thread\ThreadUpdateRequest;
use App\Models\Thread;
use App\Models\Channel;
use Illuminate\Http\Request;
use App\Transformers\ThreadTransformer;

class ThreadsController extends Controller
{
    /**
     * Display a listing of the Threads in a Channel.
     *
     * @param \App\Models\Channel $channel
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Channel $channel)
    {
        $data = $channel->threads()->orderBy('updated_at', 'DESC');

        return paginated_response($data, 'ThreadTransformer', ['owner']);
    }

    /**
     * Store a newly created Thread in storage.
     *
     * @param  \App\Http\Requests\ThreadCreateRequest
     * @param \App\Models\Channel $channel
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ThreadCreateRequest $request, Channel $channel)
    {
        $thread = $channel->addThread([
            'title' => request('title'),
            'body' => request('body'),
            'user_id' => auth()->user()->id
        ]);

        return response()->json(fractal()
            ->item($thread)
            ->transformWith(new ThreadTransformer())
            ->includeOwner());
    }

    /**
     * Display the specified Thread.
     *
     * @param \App\Models\Channel $channel
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Channel $channel, Thread $thread)
    {
        return response()->json(fractal()
            ->item($thread)
            ->transformWith(new ThreadTransformer())
            ->includeOwner());
    }

    /**
     * Update the specified Thread in storage.
     *
     * @param  \App\Http\Requests\ThreadUpdateRequest
     * @param \App\Models\Channel $channel
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ThreadUpdateRequest $request, Channel $channel, Thread $thread)
    {
        $thread->update([
            'title' => request('title'),
            'body' => request('body')
        ]);

        return response()->json(fractal()
            ->item($thread)
            ->transformWith(new ThreadTransformer())
            ->includeOwner());
    }

    /**
     * Remove the specified Thread from storage.
     *
     * @param  \App\Http\Requests\ThreadDestroyRequest
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
}
