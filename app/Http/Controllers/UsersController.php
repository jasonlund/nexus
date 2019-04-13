<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserBanRequest;
use App\Http\Requests\User\UserDestroyRequest;
use App\Http\Requests\User\UserSelfUpdateRequest;
use App\Http\Requests\User\UserShowRequest;
use App\Http\Requests\User\UserUnbanRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Services\UsersService;
use Illuminate\Http\Request;
use App\Transformers\UserTransformer;
use App\Models\User;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

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
            $query->whereIs(request('role'))->orderBy('username');
        }else{
            $query = $query->orderBy('created_at');
        }

        return $paginated ? paginated_response($query, 'UserTransformer') :
            collection_response($query->get(), 'UserTransformer');
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

    /**
     * Display the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showSelf()
    {
        return item_response(auth()->user(), 'UserTransformer');
    }

    /**
     * Update the authorized User in storage.
     *
     * @param UserSelfUpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSelf(UserSelfUpdateRequest $request)
    {
        $this->service->update(auth()->user(), request()->all());

        return item_response(auth()->user(), 'UserTransformer');
    }

    /**
     * Remove the authenticated User from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroySelf()
    {
        $this->service->delete(auth()->user());

        return response()->json([]);
    }
}
