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
use App\Services\ChannelsService;

class ChannelsController extends Controller
{
    protected $service;

    public function __construct(ChannelsService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the channels in order.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return collection_response(Channel::ordered(), 'ChannelTransformer',
            ['latest_thread', 'latest_thread.owner', 'latest_reply', 'latest_reply.owner']);
    }

    /**
     * Store a newly created channel in storage.
     *
     * @param  ChannelCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ChannelCreateRequest $request)
    {
        $channel = $this->service->create(request()->all());
        $this->service->assignModerators($channel, request('moderators'));

        return item_response($channel, 'ChannelTransformer');
    }

    /**
     * Reorder existing Channels.
     *
     * @param ChannelReorderRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(ChannelReorderRequest $request)
    {
        $this->service->reorder(request('order'));

        return collection_response(Channel::ordered(), 'ChannelTransformer');
    }

    /**
     * Display the specified Channel.
     *
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Channel $channel)
    {
        return item_response($channel, 'ChannelTransformer');
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
        $this->service->update($channel, request()->all());
        $this->service->assignModerators($channel, request('moderators'));

        return item_response($channel, 'ChannelTransformer');
    }

    public function markRead(Channel $channel)
    {
        $this->service->viewed($channel);

        return response()->json([]);
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
