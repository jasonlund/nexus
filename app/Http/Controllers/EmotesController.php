<?php

namespace App\Http\Controllers;

use App\Models\Emote;
use App\Http\Requests\Emote\EmoteCreateRequest;
use App\Http\Requests\Emote\EmoteDestroyRequest;
use Cache;

class EmotesController extends Controller
{
    /**
     * EmotesController constructor.
     *
     * @return  void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of Emotes sorted by name.
     *
     * @return  \Illuminate\Http\Response
     */
    public function index()
    {
        return response(Cache::rememberForever('emotes', function () {
            return Emote::response();
        }));
    }

    /**
     * Store a newly created Emote in storage.
     *
     * @param   EmoteCreateRequest  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function store(EmoteCreateRequest $request)
    {
        $emote = $this->service->create($request);

        return item_response($emote, 'EmoteTransformer');
    }

    /**
     * Remove the specified Emote from storage.
     *
     * @param   EmoteDestroyRequest  $request
     * @param   Emote                $emote
     *
     * @return  \Illuminate\Http\Response
     */
    public function destroy(EmoteDestroyRequest $request, Emote $emote)
    {
        $emote->delete();

        return response('', 204);
    }
}
