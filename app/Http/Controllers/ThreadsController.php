<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThreadCreateRequest;
use App\Http\Requests\ThreadDestroyRequest;
use App\Http\Requests\ThreadUpdateRequest;
use App\Models\Thread;
use App\Models\Channel;
use Illuminate\Http\Request;
use App\Transformers\ThreadTransformer;

class ThreadsController extends Controller
{
    /**
     * ThreadsController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param \App\Models\Channel $channel
     * @return \Illuminate\Http\Response
     */
    public function index(Channel $channel)
    {
        $data = $channel->threads();

        return paginated_response($data, 'ThreadTransformer', ['owner']);
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
     * @param  \App\Http\Requests\ThreadCreateRequest
     * @param \App\Models\Channel $channel
     * @return \Illuminate\Http\Response
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
     * Display the specified resource.
     *
     * @param \App\Models\Channel $channel
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function show(Channel $channel, Thread $thread)
    {
        return response()->json(fractal()
            ->item($thread)
            ->transformWith(new ThreadTransformer())
            ->includeOwner());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function edit(Thread $thread)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\ThreadUpdateRequest
     * @param \App\Models\Channel $channel
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\Response
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Http\Requests\ThreadDestroyRequest
     * @param \App\Models\Channel $channel
     * @param \App\Models\Thread
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(ThreadDestroyRequest $request, Channel $channel, Thread $thread)
    {
        $thread->delete();

        return response()->json();
    }
}
