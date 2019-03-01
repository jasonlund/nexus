<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChannelCreateRequest;
use App\Http\Requests\ChannelUpdateRequest;
use App\Models\Channel;
use Illuminate\Http\Request;
use App\Transformers\ChannelTransformer;

class ChannelsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Channel::all();

        return response()->json(fractal()
            ->collection($data)
            ->transformWith(new ChannelTransformer()));
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
     * @param  \App\Http\Requests\ChannelCreateRequest
     * @return \Illuminate\Http\Response
     */
    public function store(ChannelCreateRequest $request)
    {
        $channel = Channel::create([
            'name' => request('name'),
            'description' => request('description')
        ]);

        return response()->json(fractal()
            ->item($channel)
            ->transformWith(new ChannelTransformer()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function show(Channel $channel)
    {
        return response()->json(fractal()
            ->item($channel)
            ->transformWith(new ChannelTransformer())
            ->includeThreads());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function edit(Channel $channel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function update(ChannelUpdateRequest $request, Channel $channel)
    {
        $channel->update([
            'name' => request('name'),
            'description' => request('description')
        ]);

        return response()->json(fractal()
            ->item($channel)
            ->transformWith(new ChannelTransformer()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function destroy(Channel $channel)
    {
        $channel->delete();

        return response()->json([]);
    }
}
