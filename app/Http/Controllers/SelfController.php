<?php

namespace App\Http\Controllers;

use App\Http\Requests\Self\SelfAvatarRequest;
use App\Http\Requests\Self\SelfDestroyRequest;
use App\Http\Requests\Self\SelfUpdateRequest;

class SelfController extends Controller
{
    /**
     * The currently authenticated User
     *
     * @var \App\Models\User
     */
    protected $user;

    /**
     * SelfController constructor.
     *
     * @return  void
     */
    public function __construct()
    {
        parent::__construct('Users');
        $this->user = auth()->user();
    }

    /**
     * Display the authenticated User.
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        return item_response($this->user, 'UserTransformer');
    }

    /**
     * Update the authorized User in storage.
     *
     * @param   SelfUpdateRequest  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function update(SelfUpdateRequest $request)
    {
        $this->service->update($this->user, $request->all());

        return item_response($this->user, 'UserTransformer');
    }

    /**
     * Remove the authenticated User from storage.
     *
     * @param   SelfDestroyRequest  $request
     *
     * @return  \Illuminate\Http\Response
     */
    public function destroy(SelfDestroyRequest $request)
    {
        $this->service->delete($this->user);

        return response('', 204);
    }

    /**
     * Update the authenticated user's avatar.
     *
     * @param   SelfAvatarRequest  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function avatar(SelfAvatarRequest $request)
    {
        $this->service->avatar($this->user, $request);

        return item_response($this->user, 'UserTransformer');
    }
}
