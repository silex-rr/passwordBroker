<?php

namespace Identity\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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

    public function logout(): JsonResponse
    {
        Auth::logout();
        return new JsonResponse(['message' => 'Logged Out'], 200);
    }
}
