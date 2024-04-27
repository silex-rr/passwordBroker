<?php

namespace Identity\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Identity\Application\Http\Requests\RecoveryUserLandingRequest;
use Identity\Application\Http\Requests\RecoveryUserRequest;
use Identity\Application\Services\RecoveryUserService;
use Identity\Domain\User\Models\Attributes\RecoveryLinkType;
use Identity\Domain\User\Models\RecoveryLink;
use Identity\Domain\User\Models\User;
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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RecoveryController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        protected readonly RecoveryUserService $recoveryUserService
    )
    {
    }

    #[Patch(
        path: "/identity/api/recovery/{recoveryLink:key}",
        summary: "Recovery a User Password",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(ref: "#/components/schemas/Identity_RecoveryUserRequest"),
            )
        ),
        tags: ["Identity_RecoveryController"],
        parameters: [
            new PathParameter(
                name: "recoveryLink:key",
                required: true,
                schema: new Schema(ref: "#/components/schemas/Identity_RecoveryUserLandingRequest")
            ),
        ],
        responses: [
            new \OpenApi\Attributes\Response(
                response: Response::HTTP_OK,
                description: "User password was successfully changed"
            ),
        ],
    )]
    public function activate(RecoveryLink $recoveryLink, RecoveryUserLandingRequest $request): JsonResponse
    {
        if ($recoveryLink->exists) {
            $this->recoveryUserService->activateRecoveryLink(
                recoveryLink: $recoveryLink,
                password: $request->input('user.password'),
            );
        }

        return new JsonResponse([], Response::HTTP_OK);
    }

    #[Post(
        path: "/identity/api/recovery",
        summary: "Create an Recovery Password Link",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new Schema(ref: "#/components/schemas/Identity_RecoveryUserRequest"),
            )
        ),
        tags: ["Identity_RecoveryController"],
        responses: [
            new \OpenApi\Attributes\Response(
                response: Response::HTTP_OK,
                description: "Recovery Link was successfully created"
            ),
        ],
    )]
    public function store(RecoveryUserRequest $request): JsonResponse
    {
        /**
         * @var User|null $user
         */
        $user = User::where([
            'email' => $request->input('user.email')
        ])->first();

        if ($user) {
            $recoveryLink = $this->recoveryUserService->createRecoveryLink(
                user: $user,
                linkType: RecoveryLinkType::RECOVERY,
                fingerprintFront: $request->get('fingerprint'),
            );
            $this->dispatchSync(new CreateRecoveryLink($recoveryLink));
        }

        return new JsonResponse([], Response::HTTP_OK);
    }
}
