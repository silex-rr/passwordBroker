<?php

namespace Identity\Application\Http\Controllers;

use http\Client\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserAuthController
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            Auth::user();
            return new JsonResponse(['message' => 'Login successful'], 200);
        } else {
            return new JsonResponse(['message' => 'Invalid email or password'], 401);
        }
    }

    public function logout(): JsonResponse
    {
        Auth::logout();
        return new JsonResponse(['message' => 'Logged Out'], 200);
    }
}
