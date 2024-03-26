<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Identity\Domain\User\Models\User;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes\Delete;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\PathParameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Http\Requests\EntryGroupUserRequest;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Groups\Admin;
use PasswordBroker\Domain\Entry\Models\Groups\Member;
use PasswordBroker\Domain\Entry\Models\Groups\Moderator;
use PasswordBroker\Domain\Entry\Services\AddAdminToEntryGroup;
use PasswordBroker\Domain\Entry\Services\AddMemberToEntryGroup;
use PasswordBroker\Domain\Entry\Services\AddModeratorToEntryGroup;
use PasswordBroker\Domain\Entry\Services\RemoveAdminFromEntryGroup;
use PasswordBroker\Domain\Entry\Services\RemoveMemberFromEntryGroup;
use PasswordBroker\Domain\Entry\Services\RemoveModeratorFromEntryGroup;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryGroupUserValidationHandler;
use phpseclib3\Exception\NoKeyLoadedException;
use RuntimeException;

class EntryGroupUserController extends Controller
{
    public function __construct(private readonly EntryGroupService $entryGroupService)
    {
        $this->authorizeResource(EntryGroup::class, ['entryGroup']);
    }

    protected function resourceAbilityMap(): array
    {
        return [
            'index' => 'viewAnyRole',
            'show' => 'viewRole',
            'create' => 'createRole',
            'store' => 'createRole',
            'edit' => 'updateRole',
            'update' => 'updateRole',
            'destroy' => 'deleteRole'
        ];
    }

    #[Get(
        path: "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/users/",
        summary: "Get a List of users who belongs to that EntryGroup",
        tags: ["PasswordBroker_EntryGroupUserController"],
        parameters: [
            new PathParameter(
                name: "entryGroup:entry_group_id",
                required: true,
                schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")
            ),
        ],
        responses: [
            new Response(
                response: 200,
                description: "List of users",
                content: new JsonContent(
                    type: "array",
                    items: new Items(
                        oneOf: [
                            new Schema(ref: "#/components/schemas/PasswordBroker_Role_Admin"),
                            new Schema(ref: "#/components/schemas/PasswordBroker_Role_Moderator"),
                            new Schema(ref: "#/components/schemas/PasswordBroker_Role_Member"),
                        ],
                    ),
                ),
            )
        ],
    )]
    public function index(EntryGroup $entryGroup): JsonResponse
    {
        return new JsonResponse($entryGroup->users(), 200);
    }

    #[Post(
        path: "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/users/",
        summary: "Add a User to an EntryGroup with a Role",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupUserRequest")
            ),
        ),
        tags: ["PasswordBroker_EntryGroupUserController"],
        parameters: [
            new PathParameter(
                name: "entryGroup:entry_group_id",
                required: true,
                schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")
            ),
        ],
        responses: [
            new Response(
                response: 200,
                description: "User successfully add to the EntryGroup",
            ),
            new Response(
                response: 422,
                description: "Wrong master_password",
            ),
        ],
    )]
    public function store(EntryGroup $entryGroup, EntryGroupUserRequest $request): JsonResponse
    {
        $params = [
            $request->targetUser(),
            $entryGroup,
            $request->get('encrypted_aes_password'),
            $request->get('master_password'),
            new EntryGroupUserValidationHandler()
        ];
        switch ($request->get('role')) {
            case Admin::ROLE_NAME:
                $job = new AddAdminToEntryGroup(...$params);
                break;
            case Moderator::ROLE_NAME:
                $job = new AddModeratorToEntryGroup(...$params);
                break;
            case Member::ROLE_NAME:
                $job = new AddMemberToEntryGroup(...$params);
                break;
        }

        if (!isset($job)) {
            throw new RuntimeException("Undefined Role Service");
        }

        try {
            $this->dispatchSync($job);
        }  catch (NoKeyLoadedException $exception) {
            return new JsonResponse([
                'message' => "MasterPassword is invalid",
                'errors' => [
                    'master_password' => 'invalid',
                ]
            ], 422);
        }
        return new JsonResponse(null, 200);
    }

    public function show(): JsonResponse
    {
        return new JsonResponse([], 200);
    }

    public function update(): JsonResponse
    {
        return new JsonResponse([], 200);
    }

    #[Delete(
        path: "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/users/{user:user_id}",
        summary: "Remove a User from an EntryGroup",
        tags: ["PasswordBroker_EntryGroupUserController"],
        parameters: [
            new PathParameter(
                name: "entryGroup:entry_group_id",
                required: true,
                schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId"),
            ),
            new PathParameter(
                name: "user:user_id",
                required: true,
                schema: new Schema(ref: "#/components/schemas/Identity_UserId"),
            ),
        ],
        responses: [
            new Response(response: 200, description: "User successfully removed from the EntryGroup",),
            new Response(response: 404, description: "User was not found in the EntryGroup",),
        ],
    )]
    public function destroy(EntryGroup $entryGroup, User $user): JsonResponse
    {
        /**
         * @var Admin $admin
         */
        $admin = $entryGroup->admins()->where('user_id', $user->user_id)->first();
        if (!is_null($admin)) {
            $this->dispatchSync(new RemoveAdminFromEntryGroup($admin, $entryGroup));
            return new JsonResponse(null, 200);
        }
        /**
         * @var Moderator $moderator
         */
        $moderator = $entryGroup->moderators()->where('user_id', $user->user_id)->first();
        if (!is_null($moderator)) {
            $this->dispatchSync(new RemoveModeratorFromEntryGroup($moderator, $entryGroup));
            return new JsonResponse(null, 200);
        }
        /**
         * @var Member $member
         */
        $member = $entryGroup->members()->where('user_id', $user->user_id)->first();
        if (!is_null($member)) {
            $this->dispatchSync(new RemoveMemberFromEntryGroup($member, $entryGroup));
            return new JsonResponse(null, 200);
        }
        abort(404, 'User was not found in the Entry Group');
    }
}
