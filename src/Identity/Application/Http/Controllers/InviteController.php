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
use Identity\Domain\User\Services\CreateRecoveryLink;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Patch;
use OpenApi\Attributes\PathParameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Schema;
use phpseclib3\Crypt\Random;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class InviteController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        protected readonly UserRegistrationService $registrationService,
        protected readonly UserService             $userService,
        protected readonly RecoveryUserService     $recoveryUserService,
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
                schema: new Schema(ref: "#/components/schemas/Identity_RecoveryUserLandingRequest")
            ),
        ],
        responses: [
            new \OpenApi\Attributes\Response(
                response: 200,
                description: "User was successfully registered"
            ),
        ],
    )]
    public function activate(?RecoveryLink $recoveryLink, InviteUserLandingRequest $request): JsonResponse
    {
        if ($recoveryLink) {
            $this->recoveryUserService->activateRecoveryLink(
                recoveryLink: $recoveryLink,
                password: $request->input('user.password'),
                username: $request->input('user.username'),
                master_password: $request->input('user.master_password')
            );
        }

        return new JsonResponse([], Response::HTTP_OK);
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
        $user = $this->registrationService->execute(
            email: $request->input('user.email'),
            username: $request->input('user.username') ?? $this->userService->getUserUniqTemporaryName(),
            password: Random::string(32),
            master_password: Random::string(32),
        );

        $recoveryLink = $this->recoveryUserService->createRecoveryLink(
            user: $user,
            linkType: RecoveryLinkType::INVITE,
            fingerprintFront: $request->get('fingerprint'),
        );

        $this->dispatchSync(new CreateRecoveryLink($recoveryLink));

        return new JsonResponse(
            ['inviteLinkUrl' => $this->recoveryUserService->makeRecoveryUrl($recoveryLink)],
            Response::HTTP_OK,
        );
    }
}
