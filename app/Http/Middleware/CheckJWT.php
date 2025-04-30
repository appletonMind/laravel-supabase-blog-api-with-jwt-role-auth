<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class CheckJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Verify that the JWT token is valid
            $user = JWTAuth::parseToken()->authenticate();

            // If the token is valid, we can continue with the request.
            return $next($request);
        } catch (JWTException $e) {
            // If the token is invalid or does not exist, return error
            return response()->json(['message' => 'Token no válido'], 401);
        }
    }
}
