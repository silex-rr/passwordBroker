<?php

namespace Identity\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Identity\Application\Http\Requests\GetOrCreateTokenRequest;
use Identity\Application\Http\Requests\LoginRequest;
use Identity\Domain\User\Models\User;
use Identity\Domain\User\Services\GetUserToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;

class UserAuthController extends Controller
{
    #[Post(
        path: "/identity/api/login",
        summary: "Authentication endpoint",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(ref: "#/components/schemas/Identity_LoginRequest",),
            ),
        ),
        tags: ["Identity_UserAuthController"],
        responses: [
            new Response(
                response: 200,
                description: "Provide auth token if token_is_required was true",
                content: new JsonContent(
                    properties: [
                        new Property(
                            property: "message",
                            type: "string",
                            default: "Login successful",
                        ),
                        new Property(
                            property: "token",
                            description: "provided only if token_is_required was true",
                            type: "string",
                            nullable: true
                        ),
                        new Property(
                            property: "user",
                            ref: "#/components/schemas/Identity_User",
                            description: "provided only if token_is_required was true",
                            type: "object",
                        ),
                    ],
                    type: "object",
                ),
            ),
            new Response(
                response: 401,
                description: "Failed login attempt",
                content: new JsonContent(
                    properties: [
                        new Property(property: "message", type: "string", default: "Invalid email or password"),
                    ],
                    type: "object",
                ),
            ),
        ],
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            /**
             * @var User $user
             */
            $user = Auth::user();
            $response = ['message' => 'Login successful'];

            if($request->isTokenRequired()) {

                $response['token'] = $this->dispatchSync(new GetUserToken(
                    user: $user,
                    token_name: $request->getTokenName(),
                ));
                $response['user'] = $user;
            }
            return new JsonResponse($response, 200);
        }

        return new JsonResponse(['message' => 'Invalid email or password'], 401);
    }

    #[Get(
        path: "/identity/api/logout",
        summary: "Logout endpoint",
        tags: ["Identity_UserAuthController"],
        responses: [
            new Response(
                response: 200,
                description: "Logged out successfully",
                content: new JsonContent(
                    properties: [
                        new Property(property: "message", type: "string", default: "Logged Out",),
                    ],
                    type: "object",
                ),
            ),
        ],
    )]
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

    #[Post(
        path: "/identity/api/token",
        summary: "Get a token",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(ref: "#/components/schemas/Identity_GetOrCreateTokenRequest",),
            ),
        ),
        tags: ["Identity_UserAuthController"],
        responses: [
            new Response(
                response: 200,
                description: "Auth Token and User object",
                content: new JsonContent(
                    properties: [
                        new Property(property: "token", type: "string",),
                        new Property(property: "user", ref: "#/components/schemas/Identity_User",),
                    ],
                    type: "object",
                ),
            ),
        ],
    )]
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

    #[Get(
        path: "/identity/api/me",
        summary: "Return a logged in user",
        tags: ["Identity_UserAuthController"],
        responses: [
            new Response(
                response: 200,
                description: "Logged in User or message guest|firstUser, firstUser - system does not have any registered user",
                content: new JsonContent(
                    properties: [
                        new Property(
                            property: "message",
                            type: "string",
                            enum: ["loggedIn", "guest", "firstUser"],
                        ),
                        new Property(property: "user", ref: "#/components/schemas/Identity_User", nullable: true,),
                    ],
                    type: "object",
                ),
            ),
        ],
    )]
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
