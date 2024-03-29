<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UsersService;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Handle a registration request for the application.
     *
     * @param   Request  $request
     *
     * @return  \Illuminate\Http\JsonResponse
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
     * @param   array  $data
     *
     * @return  Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, UsersService::validationRules('register'));
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param   array  $data
     *
     * @return  User
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
     * @param   Request  $request
     * @param   User     $user
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    protected function registered(Request $request, User $user)
    {
        return response()->json([
            'access_token' => \JWTAuth::fromUser($user),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
