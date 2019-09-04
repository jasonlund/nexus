<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Get the response for a successful password reset link.
     *
     * @param   Request  $request
     * @param   string   $response
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return response()->json($response);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param   Request  $request
     * @param   string   $response
     *
     * @return  void
     *
     * @throws  ValidationException
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        throw ValidationException::withMessages([
            'email' => 'This is not a valid email address.'
        ]);
    }
}
