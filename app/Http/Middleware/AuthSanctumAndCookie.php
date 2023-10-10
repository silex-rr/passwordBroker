<?php

namespace App\Http\Middleware;

use Closure;
use Identity\Domain\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\Request;

class AuthSanctumAndCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
//        $token = $request->bearerToken();
//        $model = new Sanctum::$personalAccessTokenModel;
//
////        /**
////         * @var User $u
////         */
////        $u = User::where('name', 'silex')->first();
//
////        $newAccessToken = $u->createToken('test2');
////        $plainTextToken = $newAccessToken->plainTextToken;
//
//
//        $accessToken = $model::findToken(hash('sha256', $token));
////        $tokenable = $accessToken->tokenable->withAccessToken(
////            $accessToken
////        );
//        var_dump(
//            $token,
////            $plainTextToken,
//            $accessToken,
////            $tokenable,
//            $model->getKeyType(),
//            Auth::guard('sanctum')->user(),
//            Auth::guard('api')->user()
//        );die;

        if (Auth::guard('sanctum')->check()) {
            Auth::setUser(Auth::guard('sanctum')->user());
            return $next($request);
        }

        if (Auth::guard('api')->check()) {
            return $next($request);
        }

        Auth::guest();
        return $next($request);
    }
}
