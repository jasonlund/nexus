<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChannelCategory\ChannelCategoryCreateRequest;
use App\Models\ChannelCategory;
use App\Http\Requests\ChannelCategory\ChannelCategoryUpdateRequest;
use App\Http\Requests\ChannelCategory\ChannelCategoryDestroyRequest;
use App\Http\Requests\ChannelCategory\ChannelCategoryReorderRequest;

class ChannelCategoriesController extends Controller
{
    /**
     * ChannelCategoriesContoller constructor.
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
     * @return  \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return collection_response(
            ChannelCategory::ordered(),
            'ChannelCategoryTransformer',
            [
                'channels', 'channels.latest_thread', 'channels.latest_thread.owner', 'channels.latest_reply',
                'channels.latest_reply.owner'
            ]
        );
    }

    /**
     * Store a newly created ChannelCategory in storage.
     *
     * @param   ChannelCategoryCreateRequest  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function store(ChannelCategoryCreateRequest $request)
    {
        $category = $this->service->create($request->all());

        return item_response($category, 'ChannelCategoryTransformer');
    }

    /**
     * Update the specified Channel in storage.
     *
     * @param   ChannelCategoryUpdateRequest  $request
     * @param   ChannelCategory               $category
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function update(ChannelCategoryUpdateRequest $request, ChannelCategory $category)
    {
        $this->service->update($category, $request->all());

        return item_response($category, 'ChannelCategoryTransformer');
    }

    /**
     * Reorder existing ChannelsCategories.
     *
     * @param   ChannelCategoryReorderRequest  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function reorder(ChannelCategoryReorderRequest $request)
    {
        $this->service->reorder($request->order);

        return collection_response(
            ChannelCategory::ordered(),
            'ChannelCategoryTransformer'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param   ChannelCategoryDestroyRequest  $request
     * @param   ChannelCategory                $category
     *
     * @return  \Illuminate\Http\Response
     */
    public function destroy(ChannelCategoryDestroyRequest $request, ChannelCategory $category)
    {
        $category->delete();

        return response('', 204);
    }
}