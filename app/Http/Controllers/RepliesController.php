<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reply\ReplyCreateRequest;
use App\Http\Requests\Reply\ReplyDestroyRequest;
use App\Http\Requests\Reply\ReplyUpdateRequest;
use App\Models\ChannelCategory;
use App\Models\Channel;
use App\Models\Reply;
use App\Models\Thread;

class RepliesController extends Controller
{
    /**
     * RepliesController constructor.
     *
     * @return  void
     */
    public function __construct()
    {
        $this->middleware('limit.actions')->only(['store']);
        parent::__construct();
    }

    /**
     * Display a paginated listing of a thread's replies in order.
     *
     * @param   ChannelCategory $category
     * @param   Channel         $channel
     * @param   Thread          $thread
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function index(ChannelCategory $category, Channel $channel, Thread $thread)
    {
        return paginated_response(
            $thread->replies(),
            'ReplyTransformer',
            ['owner', 'editor']
        );
    }

    /**
     * Store a newly created Reply in storage.
     *
     * @param   ReplyCreateRequest  $request
     * @param   ChannelCategory     $category
     * @param   Channel             $channel
     * @param   Thread              $thread
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function store(ReplyCreateRequest $request, ChannelCategory $category, Channel $channel, Thread $thread)
    {
        $reply = $this->service->create($thread, $request->all());

        return item_response($reply, 'ReplyTransformer', ['owner', 'editor']);
    }

    /**
     * Update the specified Reply in storage.
     *
     * @param   ReplyUpdateRequest  $request
     * @param   ChannelCategory     $category
     * @param   Channel             $channel
     * @param   Thread              $thread
     * @param   Reply               $reply
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function update(
        ReplyUpdateRequest $request,
        ChannelCategory $category,
        Channel $channel,
        Thread $thread,
        Reply $reply
    ) {
        $this->service->update($reply, $request->all());

        return item_response($reply, 'ReplyTransformer', ['owner', 'editor']);
    }

    /**
     * Remove the specified Reply from storage.
     *
     * @param   ReplyDestroyRequest  $request
     * @param   ChannelCategory      $category
     * @param   Channel              $channel
     * @param   Thread               $thread
     * @param   Reply                $reply
     *
     * @return  \Illuminate\Http\Response
     */
    public function destroy(
        ReplyDestroyRequest $request,
        ChannelCategory $category,
        Channel $channel,
        Thread $thread,
        Reply $reply
    ) {
        $reply->delete();

        return response('', 204);
    }
}
