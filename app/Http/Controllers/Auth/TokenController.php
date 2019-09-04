<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use App\Http\Requests\User\UserLoginRequest;

class TokenController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @param  UserLoginRequest     $request
     *
     * @return  \Illuminate\Http\JsonResponse
     *
     * @throws  ValidationException
     */
    public function login(UserLoginRequest $request)
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return  \Illuminate\Http\Response
     */
    public function logout()
    {
        auth()->logout();

        return response('', 204);
    }

    /**
     * Refresh a token.
     *
     * @return  \Illuminate\Http\Response
     */
    public function refresh()
    {
        try {
            return $this->respondWithToken(auth()->refresh());
        } catch (TokenExpiredException $e) {
            return response(['message' => 'Token Expired'], 403);
        } catch (TokenBlacklistedException $e) {
            return response(['message' => 'Token Expired'], 403);
        }
    }

    /**
     * Respond with a new token.
     *
     * @param   string  $token
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([])
            ->header('Authorization', 'Bearer ' . $token);
    }
}
