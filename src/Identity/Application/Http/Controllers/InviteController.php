<?php

namespace Identity\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Identity\Application\Http\Requests\InviteUserLandingRequest;
use Identity\Application\Http\Requests\InviteUserRequest;
use Identity\Application\Services\RecoveryUserService;
use Identity\Application\Services\UserRegistrationService;
use Identity\Application\Services\UserService;
use Identity\Domain\User\Models\Attributes\RecoveryLinkType;
use Identity\Domain\User\Models\RecoveryLink;
use Identity\Domain\User\Models\User;
use Identity\Domain\User\Services\CreateRecoveryLink;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Patch;
use OpenApi\Attributes\PathParameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Schema;
use PasswordBroker\Infrastructure\Services\PasswordGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class InviteController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        protected readonly UserRegistrationService $registrationService,
        protected readonly UserService             $userService,
        protected readonly RecoveryUserService     $recoveryUserService,
        protected readonly PasswordGenerator       $passwordGenerator,
    )
    {
    }

    #[Patch(
        path: "/identity/api/invite/{recoveryLink:key}",
        summary: "Registration new User by Invitation Link",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(ref: "#/components/schemas/Identity_InviteUserRequest"),
            )
        ),
        tags: ["Identity_InviteController"],
        parameters: [
            new PathParameter(
                name: "recoveryLink:key",
                required: true,
                schema: new Schema(ref: "#/components/schemas/Identity_InviteUserLandingRequest")
            ),
        ],
        responses: [
            new \OpenApi\Attributes\Response(
                response: 200,
                description: "User was successfully registered"
            ),
        ],
    )]
    public function activate(RecoveryLink $recoveryLink, InviteUserLandingRequest $request): JsonResponse
    {
        if ($recoveryLink->exists) {
            $this->recoveryUserService->activateRecoveryLink(
                recoveryLink: $recoveryLink,
                password: $request->input('user.password'),
                username: $request->input('user.username'),
                master_password: $request->input('user.master_password')
            );
            return new JsonResponse([], Response::HTTP_OK);
        }

        return new JsonResponse([], Response::HTTP_BAD_REQUEST);
    }

    #[Post(
        path: "/identity/api/invite",
        summary: "Create an Invitation Link",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new Schema(ref: "#/components/schemas/Identity_InviteUserRequest"),
            )
        ),
        tags: ["Identity_InviteController"],
        responses: [
            new \OpenApi\Attributes\Response(
                response: 200,
                description: "Invitation Link was successfully created"
            ),
        ],
    )]
    public function store(InviteUserRequest $request): JsonResponse
    {
        $this->passwordGenerator->setLength(30);
        $user = $this->registrationService->execute(
            email: $request->input('user.email'),
            username: $request->input('user.username') ?? $this->userService->getUserUniqTemporaryName(),
            password: $this->passwordGenerator->generate(),
            master_password: $this->passwordGenerator->generate(),
        );

        $recoveryLink = $this->recoveryUserService->createRecoveryLink(
            user: $user,
            linkType: RecoveryLinkType::INVITE,
            fingerprintFront: $request->get('fingerprint'),
        );

        $this->dispatchSync(new CreateRecoveryLink($recoveryLink));

        return new JsonResponse(
            [
                'inviteLinkUrl' => $this->recoveryUserService->makeRecoveryUrl($recoveryLink),
                'key' => $recoveryLink->key,
            ],
            Response::HTTP_OK,
        );
    }

    #[Get(
        path: "/identity/api/invite",
        summary: "Show an info for Invitation Link",
        tags: ["Identity_InviteController"],
        responses: [
            new \OpenApi\Attributes\Response(
                response: 200,
                description: "Invite Link Data",
                content: new JsonContent(
                    properties: [
                        new Property(
                            property: "name",
                            type: "string",
                        ),
                        new Property(
                            property: "email",
                            type: "string",
                        ),
                    ],
                    type: "object",
                )
            )
        ]
    )]
    public function show(RecoveryLink $recoveryLink): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = $recoveryLink->user()->first();

        return new JsonResponse([
            'name' => $user->name,
            'email' => $user->email,
        ], Response::HTTP_OK);
    }
}
