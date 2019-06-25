<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserAvatarRequest;
use App\Http\Requests\User\UserBanRequest;
use App\Http\Requests\User\UserDestroyRequest;
use App\Http\Requests\User\UserShowRequest;
use App\Http\Requests\User\UserUnbanRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Services\UsersService;
use App\Models\User;

class UsersController extends Controller
{
    protected $service;

    public function __construct(UsersService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the Users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // TODO -- clean this up. a new controller method for listing by role?
        $paginated = true;
        $query = User::query();
        if(request()->has('role')
            && in_array(request('role'), ['admin', 'super-moderator', 'moderator'])){
            $paginated = false;
            $query->whereIs(request('role'));
        }else if(request()->has('active')){
            $paginated = false;
            $query->where('last_active_at', '>=', now()->subMinutes(10));
        }

        $query->orderBy('username');

        return $paginated ? paginated_response($query, 'UserTransformer') :
            collection_response($query, 'UserTransformer');
    }

    /**
     * Display the specified User.
     *
     * @param UserShowRequest $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(UserShowRequest $request, User $user)
    {
        return item_response($user, 'UserTransformer');
    }

    /**
     * Update the specified User in storage.
     *
     * @param UserUpdateRequest $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $this->service->update($user, request()->all());
        $this->service->assignRole($user, request('role'));

        return item_response($user, 'UserTransformer');
    }

    /**
     * Remove the specified User from storage.
     *
     * @param UserDestroyRequest $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(UserDestroyRequest $request, User $user)
    {
        $this->service->delete($user, auth()->user());

        return response()->json([]);
    }

    /**
     * Restrict the specified user from participating.
     *
     * @param UserBanRequest $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function ban(UserBanRequest $request, User $user)
    {
        $this->service->ban($user, request()->all());

        return item_response($user->fresh(), 'UserTransformer');
    }

    /**
     * Derestrict the specified user from participating.
     *
     * @param UserUnbanRequest $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function unban(UserUnbanRequest $request, User $user)
    {
        $this->service->unban($user);

        return item_response($user->fresh(), 'UserTransformer');
    }

    public function avatar(UserAvatarRequest $request, User $user)
    {
        $this->service->avatar($user, request());

        return item_response($user, 'UserTransformer');
    }
}
