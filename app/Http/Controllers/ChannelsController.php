<?php

namespace App\Http\Controllers;

use App\Http\Requests\Channel\ChannelCreateRequest;
use App\Http\Requests\Channel\ChannelDestroyRequest;
use App\Http\Requests\Channel\ChannelImageRequest;
use App\Http\Requests\Channel\ChannelImageDestroyRequest;
use App\Http\Requests\Channel\ChannelReorderRequest;
use App\Http\Requests\Channel\ChannelUpdateRequest;
use App\Models\Channel;
use App\Models\ChannelCategory;

class ChannelsController extends Controller
{
    /**
     * ChannelsController constructor.
     *
     * @return  void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the Channels in order, grouped by ChannelCategory.
     *
     * @param   ChannelCategory  $category
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function index(ChannelCategory $category)
    {
        return collection_response(
            $category->channels()->ordered(),
            'ChannelTransformer',
            ['latest_thread', 'latest_thread.owner', 'latest_reply', 'latest_reply.owner']
        );
    }

    /**
     * Store a newly created Channel in storage.
     *
     * @param   ChannelCreateRequest  $request
     * @param   ChannelCategory       $category
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function store(ChannelCreateRequest $request, ChannelCategory $category)
    {
        $channel = $this->service->create($category, $request);
        $this->service->assignModerators($channel, request('moderators'));

        return item_response($channel, 'ChannelTransformer');
    }

    /**
     * Display the specified Channel.
     *
     * @param   Channel          $channel
     * @param   ChannelCategory  $category
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function show(ChannelCategory $category, Channel $channel)
    {
        return item_response(
            $channel,
            'ChannelTransformer'
        );
    }

    /**
     * Update the specified Channel in storage.
     *
     * @param   ChannelUpdateRequest  $request
     * @param   ChannelCategory       $category
     * @param   Channel               $channel
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function update(ChannelUpdateRequest $request, ChannelCategory $category, Channel $channel)
    {
        $this->service->update($channel, $request->all());
        $this->service->assignModerators($channel, request('moderators'));

        return item_response($channel, 'ChannelTransformer');
    }

    public function image(ChannelImageRequest $request, ChannelCategory $category, Channel $channel)
    {
        $this->service->image($channel, $request);

        return item_response($channel, 'ChannelTransformer');
    }

    public function imageDestroy(ChannelImageDestroyRequest $request, ChannelCategory $category, Channel $channel)
    {
        $this->service->image($channel, $request);

        return item_response($channel, 'ChannelTransformer');
    }

    /**
     * Reorder existing Channels.
     *
     * @param   ChannelReorderRequest  $request
     * @param   ChannelCategory        $category
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function reorder(ChannelReorderRequest $request, ChannelCategory $category)
    {
        $this->service->reorder(request('order'));

        return collection_response($category->channels()->ordered(), 'ChannelTransformer');
    }

    /**
     * Mark all threads in the given channel as viewed.
     *
     * @param   ChannelCategory  $category
     * @param   Channel          $channel
     *
     * @return  \Illuminate\Http\Response
     */
    public function markRead(ChannelCategory $category, Channel $channel)
    {
        $this->service->viewed($channel);

        return response('', 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param   ChannelDestroyRequest  $request
     * @param   ChannelCategory        $category
     * @param   Channel                $channel
     *
     * @return  \Illuminate\Http\Response
     */
    public function destroy(ChannelDestroyRequest $request, ChannelCategory $category, Channel $channel)
    {
        $this->service->destroy($channel);

        return response('', 204);
    }
}
