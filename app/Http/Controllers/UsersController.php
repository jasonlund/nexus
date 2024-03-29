<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserAvatarRequest;
use App\Http\Requests\User\UserBanRequest;
use App\Http\Requests\User\UserDestroyRequest;
use App\Http\Requests\User\UserUnbanRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\User;

class UsersController extends Controller
{
    /**
     * UsersController constructor.
     *
     * @return  void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the Users.
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->service->index();
    }

    /**
     * Display the specified User.
     *
     * @param   User             $user
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        return item_response($user, 'UserTransformer');
    }

    /**
     * Update the specified User in storage.
     *
     * @param   UserUpdateRequest  $request
     * @param   User               $user
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $this->service->update($user, $request->all());
        $this->service->assignRole($user, request('role'));

        return item_response($user, 'UserTransformer');
    }

    /**
     * Remove the specified User from storage.
     *
     * @param   UserDestroyRequest  $request
     * @param   User                $user
     *
     * @return  \Illuminate\Http\Response
     */
    public function destroy(UserDestroyRequest $request, User $user)
    {
        $this->service->delete($user, auth()->user());

        return response('', 204);
    }

    /**
     * Restrict the specified user from participating.
     *
     * @param   UserBanRequest  $request
     * @param   User            $user
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function ban(UserBanRequest $request, User $user)
    {
        $this->service->ban($user, $request->all());

        return item_response($user->fresh(), 'UserTransformer');
    }

    /**
     * Derestrict the specified user from participating.
     *
     * @param   UserUnbanRequest  $request
     * @param   User              $user
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function unban(UserUnbanRequest $request, User $user)
    {
        $this->service->unban($user);

        return item_response($user->fresh(), 'UserTransformer');
    }

    /**
     * Update the user's avatar.
     *
     * @param   UserAvatarRequest  $request
     * @param   User               $user
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function avatar(UserAvatarRequest $request, User $user)
    {
        $this->service->avatar($user, $request);

        return item_response($user, 'UserTransformer');
    }
}
