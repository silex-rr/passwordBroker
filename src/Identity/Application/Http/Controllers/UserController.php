<?php

namespace Identity\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Identity\Application\Http\Requests\RegisterUserRequest;
use Identity\Application\Http\Requests\UpdateUserRequest;
use Identity\Application\Http\Requests\UsersSearchRequest;
use Identity\Application\Services\RsaService;
use Identity\Application\Services\UserRegistrationService;
use Identity\Application\Services\UserService;
use Identity\Domain\User\Models\User;
use Identity\Domain\User\Models\UserAccessToken;
use Identity\Domain\User\Services\DestroyUser;
use Identity\Domain\User\Services\SearchUsers;
use Identity\Domain\User\Services\UpdateUser;
use Identity\Domain\User\Services\UserApplicationChangeOfflineDatabaseRequiredUpdate;
use Identity\Domain\User\Services\UserApplicationChangeRsaPrivateRequiredUpdate;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseRequiredUpdate;
use Identity\Domain\UserApplication\Models\Attributes\IsRsaPrivateRequiredUpdate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes\Delete;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\PathParameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Put;
use OpenApi\Attributes\QueryParameter;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Services\EncryptionService;
use Symfony\Component\Mime\Encoder\Base64Encoder;

class UserController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        protected readonly UserRegistrationService $registrationService
    )
    {
        $this->authorizeResource(User::class, ['user']);
    }

    #[Get(
        path: "/identity/api/users/search",
        summary: "Users list",
        tags: ["Identity_UserController"],
        parameters: [
            new QueryParameter(
                name: "q",
                required: false,
                schema: new Schema(ref: "#/components/schemas/Identity_UsersSearchRequest_q")
            ),
            new QueryParameter(
                name: "perPage",
                required: false,
                schema: new Schema(ref: "#/components/schemas/Identity_UsersSearchRequest_perPage"),
            ),
            new QueryParameter(
                name: "page",
                required: false,
                schema: new Schema(ref: "#/components/schemas/Identity_UsersSearchRequest_page"),
            ),
            new QueryParameter(
                name: "entryGroupInclude",
                required: false,
                schema: new Schema(ref: "#/components/schemas/Identity_UsersSearchRequest_entryGroupInclude"),
            ),
            new QueryParameter(
                name: "entryGroupExclude",
                required: false,
                schema: new Schema(ref: "#/components/schemas/Identity_UsersSearchRequest_entryGroupExclude"),
            ),
        ],
        responses: [
            new Response(
                response: 200,
                description: "List of user with pagination",
                content: new JsonContent(
                    allOf: [
                        new Schema(ref: "#/components/schemas/Common_Paginator",),
                        new Schema(
                            description: "User set as data type",
                            properties: [
                                new Property(
                                    property: "data",
                                    type: "array",
                                    items: new Items(ref: "#/components/schemas/Identity_User")
                                )
                            ],
                            type: "object",
                        )
                    ]
                )
            ),
        ],
    )]
    public function index(UsersSearchRequest $request): JsonResponse
    {

        $job = new SearchUsers(
            query: $request->getQuery(),
            perPage: $request->getPerPage(),
            page: $request->getPage(),
            entryGroupInclude: $request->getEntryGroupInclude(),
            entryGroupExclude: $request->getEntryGroupExclude()
        );

        return new JsonResponse($this->dispatchSync($job), 200);
    }

    #[Delete(
        path: "/identity/api/user/{user:user_id}",
        summary: "Delete a user",
        tags: ["Identity_UserController"],
        parameters: [
            new PathParameter(parameter: "{user:user_id}", ref: "#/components/schemas/Identity_UserId")
        ],
        responses: [
            new Response(
                response: 200,
                description: "User was successfully removed"
            ),
        ],
    )]
    public function destroy(User $user): JsonResponse
    {
        $this->dispatchSync(new DestroyUser($user));
        return new JsonResponse(null, 200);
    }

    #[Put(
        path: "/identity/api/user/{user:user_id}",
        summary: "Update a user",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(ref: "#/components/schemas/Identity_UpdateUserRequest"),
            )
        ),
        tags: ["Identity_UserController"],
        parameters: [
            new PathParameter(parameter: "{user:user_id}", required: true, ref: "#/components/schemas/Identity_UserId"),
        ],
        responses: [
            new Response(
                response: 200,
                description: "User was successfully updated"
            ),
        ],
    )]
    public function update(User $user, UpdateUserRequest $request): JsonResponse
    {
//        $all = $request->all();
//        dd($request->get('username'));

        $this->dispatchSync(new UpdateUser(
            userTarget: $user,
            username: $request->get('username'),
            email: $request->get('email'),
            password: $request->get('password')
        ));

        return new JsonResponse(null, 200);
    }

    #[Get(
        path: "/identity/api/user/{user:user_id}",
        summary: "Get a User",
        tags: ["Identity_UserController"],
        parameters: [
            new PathParameter(parameter: "{user:user_id}", ref: "#/components/schemas/Identity_UserId")
        ],
        responses: [
            new Response(
                response: 200,
                description: "A user object",
                content: new JsonContent(
                    ref: "#/components/schemas/Identity_User",
                    type: "object",
                ),
            )
        ],
    )]
    public function show(User $user): JsonResponse
    {
        return new JsonResponse($user, 200);
    }

    #[Post(
        path: "/identity/api/registration",
        summary: "Registration a new user",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(ref: "#/components/schemas/Identity_RegisterUserRequest"),
            )
        ),
        tags: ["Identity_UserController"],
        responses: [
            new Response(
                response: 200,
                description: "User was registered",
                content: new JsonContent(
                    ref: "#/components/schemas/Identity_User",
                    type: "object",
                )
            )
        ],
    )]
    public function store(RegisterUserRequest $request): JsonResponse
    {
        $email = $request->input('user.email');
        $username = $request->input('user.username');
        $password = $request->input('user.password');
        $master_password = $request->input('user.master_password');

        return new JsonResponse($this->registrationService->execute(
            email: $email,
            username: $username,
            password: $password,
            master_password: $master_password
        ));
    }

    #[Get(
        path: "/identity/api/getCbcSalt",
        summary: "Get a User CBC salt",
        tags: ["Identity_UserController"],
        responses: [
            new Response(
                response: 200,
                description: "Base64 encoded user CBC salt",
                content: new JsonContent(
                    properties: [
                        new Property(property: "timestamp", type: "string", format: "timestamp"),
                        new Property(property: "salt_base64", type: "string", format: "base64"),
                    ],
                    type: "object",
                )
            )
        ],
    )]
    public function getCbcSalt(EncryptionService $encryptionService, Base64Encoder $base64Encoder): JsonResponse
    {
        $carbon = Carbon::now();
        return new JsonResponse([
            'timestamp' => $carbon->timestamp,
            'salt_base64' => $base64Encoder->encodeString($encryptionService->getCbcSalt())
        ], 200);
    }

    #[Get(
        path: "/identity/api/getPrivateRsa",
        summary: "Get a User Privet RSA Key",
        tags: ["Identity_UserController"],
        responses: [
            new Response(
                response: 200,
                description: "Base64 encoded user Private RSA Key",
                content: new JsonContent(
                    properties: [
                        new Property(property: "timestamp", type: "string", format: "timestamp"),
                        new Property(property: "rsa_private_key_base64", type: "string", format: "base64"),
                    ],
                    type: "object",
                )
            )
        ],
    )]
    public function getPrivateRsa(RsaService $rsaService, Base64Encoder $base64Encoder, UserService $userService): JsonResponse
    {
        $carbon = Carbon::now();
        /**
         * @var User $authUser
         */
        $authUser = Auth::user();
        $userPrivateKeyString = $rsaService->getUserPrivateKeyString($authUser->user_id);

        /**
         * @var UserAccessToken $accessToken
         */
        $accessToken = $authUser->currentAccessToken();
        if ($accessToken) {
            $userApplication = $userService->getUserApplicationByToken($accessToken);
            if ($userApplication) {
                $this->dispatchSync(new UserApplicationChangeRsaPrivateRequiredUpdate(
                    userApplication: $userApplication,
                    isRsaPrivateRequiredUpdate: new IsRsaPrivateRequiredUpdate(false),
                    carbon: $carbon
                ));
            }
        }

        return new JsonResponse([
            'timestamp' => $carbon->timestamp,
            'rsa_private_key_base64' => $base64Encoder->encodeString($userPrivateKeyString)
        ], 200);
    }

}
