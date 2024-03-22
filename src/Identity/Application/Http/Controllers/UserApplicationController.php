<?php

namespace Identity\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Identity\Application\Http\Requests\CreateUserApplicationRequest;
use Identity\Application\Http\Requests\UpdateOfflineDatabaseModeRequest;
use Identity\Domain\User\Models\User;
use Identity\Domain\User\Models\UserAccessToken;
use Identity\Domain\UserApplication\Models\Attributes\ClientId;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseMode;
use Identity\Domain\UserApplication\Models\UserApplication;
use Identity\Domain\UserApplication\Services\CreateUserApplication;
use Identity\Domain\UserApplication\Services\UpdateOfflineDatabaseMode;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\PathParameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Put;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;


class UserApplicationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(UserApplication::class, ['userApplication']);
    }

    #[Get(
        path: "/identity/api/userApplications/{userApplication:user_application_id}",
        summary: "Detail info fro User Application",
        tags: ["Identity_UserApplicationController"],
        parameters: [
            new PathParameter(
                name: "userApplication:user_application_id",
                required: true,
                schema: new Schema(ref: "#/components/schemas/Identity_UserApplicationId"),
            )
        ],
        responses: [
            new Response(
                response: 200,
                description: "User Application Data",
                content: new JsonContent(
                    properties: [
                        new Property(property: "userApplication", ref: "#/components/schemas/Identity_UserApplication"),
                    ],
                    type: "object",
                )
            )
        ]
    )]
    public function show(UserApplication $userApplication): JsonResponse
    {
        return new JsonResponse(['userApplication' => $userApplication], 200);
    }

    #[Post(
        path: "/identity/api/userApplications",
        summary: "Create new instance of User Application",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(ref: "#/components/schemas/Identity_CreateUserApplicationRequest",
                )
            )
        ),
        tags: ["Identity_UserApplicationController"],
        responses: [
            new Response(
                response: 200,
                description: "User Application was successfully created",
                content: new JsonContent(
                    properties: [
                        new Property(property: "userApplication", ref: "#/components/schemas/Identity_UserApplication"),
                    ],
                    type: "object",
                )
            )
        ]
    )]
    public function store(CreateUserApplicationRequest $request): JsonResponse
    {
        $clientId = new ClientId($request->clientId());
        /**
         * @var User $user
         */
        $user = Auth::user();
        $userApplication = UserApplication::where('client_id', $clientId)->where('user_id', $user->user_id->getValue())
            ->first();
        if (!$userApplication) {
            /**
             * @var UserApplication $userApplication
             */
            $userApplication = $this->dispatchSync(new CreateUserApplication(user: $user, clientId: $clientId));
        }
        return new JsonResponse(['userApplication' => $userApplication], 200);
    }

    #[Get(
        path: "/identity/api/userApplication/{userApplication:user_application_id}/offlineDatabaseMode",
        summary: "Check if the UserApplication is in Offline Database mode",
        tags: ["Identity_UserApplicationController"],
        parameters:[
            new PathParameter(
                name: "userApplication:user_application_id",
                required: true,
                schema: new Schema(ref: "#/components/schemas/Identity_UserApplicationId"),
            )
        ],
        responses: [
            new Response(
                response: 200,
                description: "User Application offline database mode status is delivered",
                content: new JsonContent(
                    properties: [
                        new Property(property: "status", ref: "#/components/schemas/Identity_IsOfflineDatabaseMode"),
                    ],
                    type: "object",
                )
            )
        ]
    )]
    public function getOfflineDatabaseStatus(UserApplication $userApplication): JsonResponse
    {
        return new JsonResponse(['status' => $userApplication->is_offline_database_mode->getValue()], 200);
    }

    #[Get(
        path: "/identity/api/userApplication/{userApplication:user_application_id}/isOfflineDatabaseRequiredUpdate",
        summary: "Check if the UserApplication OfflineDatabase needs to be updated",
        tags: ["Identity_UserApplicationController"],
        parameters:[
            new PathParameter(
                name: "userApplication:user_application_id",
                required: true,
                schema: new Schema(ref: "#/components/schemas/Identity_UserApplicationId")
            ),
        ],
        responses: [
            new Response(
                response: 200,
                description: "User Application OfflineDatabase sync status is delivered",
                content: new JsonContent(
                    properties: [
                        new Property(property: "status", ref: "#/components/schemas/Identity_IsOfflineDatabaseRequiredUpdate")
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function isOfflineDatabaseRequiredUpdate(UserApplication $userApplication): JsonResponse
    {
        return new JsonResponse(['status' => $userApplication->is_offline_database_required_update->getValue()], 200);
    }

    #[Get(
        path: "/identity/api/userApplication/{userApplication:user_application_id}/isRsaPrivateRequiredUpdate",
        summary: "Check if the UserApplication RSA keys needs to be updated",
        tags: ["Identity_UserApplicationController"],
        parameters:[
            new PathParameter(
                name: "userApplication:user_application_id",
                required: true,
                schema: new Schema(ref: "#/components/schemas/Identity_UserApplicationId")
            ),
        ],
        responses: [
            new Response(
                response: 200,
                description: "User Application RSA keys sync status is delivered",
                content: new JsonContent(
                    properties: [
                        new Property(property: "status", ref: "#/components/schemas/Identity_IsRsaPrivateRequiredUpdate")
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function isRsaPrivateRequiredUpdate(UserApplication $userApplication): JsonResponse
    {
        return new JsonResponse(['status' => $userApplication->is_rsa_private_required_update->getValue()], 200);
    }

    #[Put(
        path: "/identity/api/userApplication/{userApplication:user_application_id}/offlineDatabaseMode",
        summary: "Switch UserApplication offlineDatabase mode",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(ref: "#/components/schemas/Identity_UpdateOfflineDatabaseModeRequest"),
            )
        ),
        tags: ["Identity_UserApplicationController"],
        parameters:[
            new PathParameter(
                name: "userApplication:user_application_id",
                required: true,
                schema: new Schema(ref: "#/components/schemas/Identity_UserApplicationId")
            ),
        ],
        responses: [
            new Response(response: 200, description: "UserApplication OfflineDatabaseMode switched")
        ]
    )]
    public function setOfflineDatabaseStatus(UserApplication $userApplication, UpdateOfflineDatabaseModeRequest $request): JsonResponse
    {
        $this->dispatchSync(
            new UpdateOfflineDatabaseMode($userApplication, IsOfflineDatabaseMode::fromNative($request->status()))
        );
        return new JsonResponse([], 200);
    }

    protected function resourceAbilityMap(): array
    {
        $resourceAbilityMap = parent::resourceAbilityMap();
        $resourceAbilityMap['setOfflineDatabaseStatus'] = 'update';
        $resourceAbilityMap['getOfflineDatabaseStatus'] = 'view';
        return $resourceAbilityMap;
    }

    /**
     * @param User $user
     * @return UserAccessToken
     */
    private function getCurrentAccessToken(User $user): UserAccessToken
    {
        /**
         * @var UserAccessToken $token
         */
        $token = $user->currentAccessToken();
        return $token;
    }
}
