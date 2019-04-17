<?php

namespace App\Http\Controllers;

use App\Http\Requests\Self\SelfAvatarRequest;
use App\Http\Requests\Self\SelfDestroyRequest;
use App\Http\Requests\Self\SelfUpdateRequest;
use App\Services\UsersService;
use Illuminate\Http\Request;

class SelfController extends Controller
{
    protected $service;
    protected $user;

    public function __construct(UsersService $service)
    {
        $this->service = $service;
        $this->user = auth()->user();
    }

    /**
     * Display the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        return item_response($this->user, 'UserTransformer');
    }

    /**
     * Update the authorized User in storage.
     *
     * @param SelfUpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(SelfUpdateRequest $request)
    {
        $this->service->update($this->user, request()->all());

        return item_response($this->user, 'UserTransformer');
    }

    /**
     * Remove the authenticated User from storage.
     *
     * @param SelfDestroyRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(SelfDestroyRequest $request)
    {
        $this->service->delete($this->user);

        return response()->json([]);
    }

    /**
     * Update the authenticated user's avatar.
     *
     * @param SelfAvatarRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function avatar(SelfAvatarRequest $request)
    {
        $this->service->avatar($this->user, request());

        return item_response($this->user, 'UserTransformer');
    }
}
