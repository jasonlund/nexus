<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Http\Middleware\RefreshToken as BaseMiddleware;
use Illuminate\Http\Request;

class RefreshToken extends BaseMiddleware
{
    /**
     * Refresh the JWT if it exists and is valid.
     *
     * @param   Request  $request
     * @param   Closure  $next
     *
     * @return  Closure
     *
     * @throws  UnauthorizedHttpException
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $this->checkForToken($request);
        } catch (\Exception $e) {
            return $next($request);
        }

        try {
            $token = $this->auth->parseToken()->refresh();
        } catch (JWTException $e) {
            throw new UnauthorizedHttpException('jwt-auth', $e->getMessage(), $e, $e->getCode());
        }

        $response = $next($request);

        // Send the refreshed token back to the client.
        return $this->setAuthenticationHeader($response, $token);
    }
}
