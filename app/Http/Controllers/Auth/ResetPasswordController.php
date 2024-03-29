<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Services\UsersService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * A newly generated token to be returned on successful password reset.
     *
     * @var string
     */
    protected $freshToken;

    /**
     * Get the password reset validation rules.
     *
     * @return  array
     */
    protected function rules()
    {
        return UsersService::validationRules('password.reset');
    }

    /**
     * Reset the given user's password.
     *
     * @param   User    $user
     * @param   string  $password
     *
     * @return  void
     */
    protected function resetPassword(User $user, $password)
    {
        $user->password = Hash::make($password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));

        $this->freshToken = \JWTAuth::fromUser($user);
    }

    /**
     * Return the newly generated token for the user.
     *
     * @param   Request  $request
     * @param   string   $response
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    protected function sendResetResponse(Request $request, $response)
    {
        return response()->json([
            'access_token' => $this->freshToken,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param   Request  $request
     * @param   string   $response
     *
     * @return  void
     *
     * @throws  ValidationException
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        throw ValidationException::withMessages([
            'token' => 'Invalid token.'
        ]);
    }
}
