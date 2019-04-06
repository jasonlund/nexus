<?php

namespace App\Http\Controllers;

use App\Http\Requests\Channel\ChannelCreateRequest;
use App\Http\Requests\Channel\ChannelDestroyRequest;
use App\Http\Requests\Channel\ChannelReorderRequest;
use App\Http\Requests\Channel\ChannelUpdateRequest;
use App\Models\Channel;
use Illuminate\Http\Request;
use App\Transformers\ChannelTransformer;
use App\Models\User;
use DB;

class ChannelsController extends Controller
{
    /**
     * Display a listing of the channels in order.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $data = Channel::ordered()->get();

        return response()->json(fractal()
            ->collection($data)
            ->transformWith(new ChannelTransformer()));
    }

    /**
     * Store a newly created channel in storage.
     *
     * @param  ChannelCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ChannelCreateRequest $request)
    {
        $channel = Channel::create([
            'name' => request('name'),
            'description' => request('description')
        ]);

        if(request()->has('moderators')){
            $moderators = User::whereIn('username', request('moderators'))->get();
            $channel->moderators()->sync($moderators);
        }

        return response()->json(fractal()
            ->item($channel)
            ->transformWith(new ChannelTransformer()));
    }

    /**
     * Reorder existing Channels.
     *
     * @param ChannelReorderRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(ChannelReorderRequest $request)
    {
        // TODO -- find a better way to do this. this is required bc testing using sqlite.
        if(app()->environment() !== 'testing') {
            $order = implode("','", request('order'));
            $channels = Channel::whereIn('slug', request('order'))
                ->orderByRaw(DB::raw("FIELD(slug, '$order')"))
                ->get();
        }else{
            $channels = Channel::whereIn('slug', request('order'))->get();
        }


        Channel::setNewOrder($channels->pluck('id')->toArray());

        $data = Channel::ordered()->get();

        return response()->json(fractal()
            ->collection($data)
            ->transformWith(new ChannelTransformer()));
    }

    /**
     * Display the specified Channel.
     *
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Channel $channel)
    {
        return response()->json(fractal()
            ->item($channel)
            ->transformWith(new ChannelTransformer()));
    }

    /**
     * Update the specified Channel in storage.
     *
     * @param  ChannelUpdateRequest  $request
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ChannelUpdateRequest $request, Channel $channel)
    {
        $channel->update([
            'name' => request('name'),
            'description' => request('description')
        ]);

        if(request()->has('moderators')){
            $moderators = User::whereIn('username', request('moderators'))->get();
            $channel->moderators()->sync($moderators);
        }else{
            $channel->moderators()->sync([]);
        }

        return response()->json(fractal()
            ->item($channel)
            ->transformWith(new ChannelTransformer()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ChannelDestroyRequest $request
     * @param Channel $channel
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(ChannelDestroyRequest $request, Channel $channel)
    {
        $channel->delete();

        return response()->json([]);
    }
}
