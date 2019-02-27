<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserSelfUpdateRequest;
use Illuminate\Http\Request;
use App\Transformers\UserTransformer;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
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
