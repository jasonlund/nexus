<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Services\UsersService;
use App\Transformers\UserTransformer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Carbon\Carbon;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        return $this->registered($request, $user);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, UsersService::validationRules('register'));
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'last_active_at' => Carbon::now()
        ]);
    }

    /**
     * Generate and return a token for the newly registered user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        return response()->json([
            'access_token' => \JWTAuth::fromUser($user),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
