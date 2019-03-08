<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserBanRequest;
use App\Http\Requests\UserDestroyRequest;
use App\Http\Requests\UserSelfUpdateRequest;
use App\Http\Requests\UserShowRequest;
use App\Http\Requests\UserUnbanRequest;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Http\Request;
use App\Transformers\UserTransformer;
use App\Models\User;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('index');
    }

    public function index()
    {
        $limit = 25;
        if(request()->has('limit')) {
            $input = (int)request('limit');
            if($input < 101 && $input > 9){
                $limit = $input;
            }
        }

        $data = User::paginate($limit);

        return response()->json(fractal()
            ->collection($data)
            ->paginateWith(new IlluminatePaginatorAdapter($data))
            ->transformWith(new UserTransformer()));
    }

    public function show(UserShowRequest $request, User $user)
    {
        return response()->json(fractal()
            ->item($user)
            ->transformWith(new UserTransformer()));
    }

    public function destroy(UserDestroyRequest $request, User $user)
    {
        $authedUser = auth()->user();
        auth()->setUser($user);
        auth()->logout();
        auth()->setUser($authedUser);

        $user->delete();

        return response()->json([]);
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $user->update(request()->all());

        return response()->json(fractal()
            ->item($user)
            ->transformWith(new UserTransformer()));
    }

    public function ban(UserBanRequest $request, User $user)
    {
        $user->ban(request()->all());

        return response()->json(fractal()
            ->item($user->fresh())
            ->transformWith(new UserTransformer()));
    }

    public function unban(UserUnbanRequest $request, User $user)
    {
        $user->unban();

        return response()->json(fractal()
            ->item($user->fresh())
            ->transformWith(new UserTransformer()));
    }

    public function showSelf()
    {
        return response()->json(fractal()
            ->item(auth()->user())
            ->transformWith(new UserTransformer()));
    }

    public function updateSelf(UserSelfUpdateRequest $request)
    {
        auth()->user()->update(request()->all());

        return response()->json(fractal()
            ->item(auth()->user())
            ->transformWith(new UserTransformer()));
    }

    public function destroySelf()
    {
        $user = auth()->user();
        auth()->logout($user);
        $user->delete();

        return response()->json([]);
    }
}
