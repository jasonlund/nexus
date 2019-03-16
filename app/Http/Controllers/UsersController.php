<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserBanRequest;
use App\Http\Requests\User\UserDestroyRequest;
use App\Http\Requests\User\UserSelfUpdateRequest;
use App\Http\Requests\User\UserShowRequest;
use App\Http\Requests\User\UserUnbanRequest;
use App\Http\Requests\User\UserUpdateRequest;
use Illuminate\Http\Request;
use App\Transformers\UserTransformer;
use App\Models\User;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class UsersController extends Controller
{
    /**
     * Display a listing of the Users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return paginated_response(User::query(), 'UserTransformer');
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
        return response()->json(fractal()
            ->item($user)
            ->transformWith(new UserTransformer()));
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
        $user->update(request()->all());

        return response()->json(fractal()
            ->item($user)
            ->transformWith(new UserTransformer()));
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
        $authedUser = auth()->user();
        auth()->setUser($user);
        auth()->logout();
        auth()->setUser($authedUser);

        $user->delete();

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
        $user->ban(request()->all());

        return response()->json(fractal()
            ->item($user->fresh())
            ->transformWith(new UserTransformer()));
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
        $user->unban();

        return response()->json(fractal()
            ->item($user->fresh())
            ->transformWith(new UserTransformer()));
    }

    /**
     * Display the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showSelf()
    {
        return response()->json(fractal()
            ->item(auth()->user())
            ->transformWith(new UserTransformer()));
    }

    /**
     * Update the authorized User in storage.
     *
     * @param UserSelfUpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSelf(UserSelfUpdateRequest $request)
    {
        auth()->user()->update(request()->all());

        return response()->json(fractal()
            ->item(auth()->user())
            ->transformWith(new UserTransformer()));
    }

    /**
     * Remove the authenticated User from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroySelf()
    {
        $user = auth()->user();
        auth()->logout($user);
        $user->delete();

        return response()->json([]);
    }
}
