<?php

namespace Identity\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Identity\Application\Http\Requests\GetOrCreateTokenRequest;
use Identity\Domain\User\Models\User;
use Identity\Domain\User\Services\GetUserToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserAuthController extends Controller
{
    public function login(): JsonResponse
    {
        $credentials = request()?->only('email', 'password');

        if (Auth::attempt($credentials)) {
            Auth::user();
            return new JsonResponse(['message' => 'Login successful'], 200);
        }

        return new JsonResponse(['message' => 'Invalid email or password'], 401);
    }

    public function logout(Request $request): JsonResponse
    {
        Session::flush();
        Auth::logout();
        Auth::guard('api')->logout();
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return new JsonResponse(['message' => 'Logged Out'], 200);
    }

    public function getToken(GetOrCreateTokenRequest $request): JsonResponse
    {
//        /**
//         * @var User $user
//         */
////        $user = Auth::user();
//
//        return new JsonResponse([
////            $user,
//        $request->user()]);

        /**
         * @var User $user
         */
        $user = $request->user();
        $token = $this->dispatchSync(new GetUserToken(
            user: $user,
            token_name: $request->get('token_name'),
        ));

        return new JsonResponse(['token' => $token, 'user' => $user]);
    }

    public function show(): JsonResponse
    {
        $response = [
            'message' => 'loggedIn',
            'user' => Auth::user()
        ];

        if (is_null($response['user'])) {
            $response['message'] = User::exists() ? 'guest' : 'firstUser';
            return new JsonResponse($response, 200);
        }
        return new JsonResponse($response, 200);
    }
}
