<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Identity\Application\Services\UserService;
use Identity\Domain\User\Models\User;
use Identity\Domain\User\Models\UserAccessToken;
use Identity\Domain\User\Services\UserApplicationChangeOfflineDatabaseRequiredUpdate;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseRequiredUpdate;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes\Delete;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Patch;
use OpenApi\Attributes\PathParameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Put;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Http\Requests\EntryGroupMoveRequest;
use PasswordBroker\Application\Http\Requests\EntryGroupRequest;
use PasswordBroker\Application\Http\Requests\EntryGroupUpdateRequest;
use PasswordBroker\Application\Services\EncryptionService;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Groups\Admin;
use PasswordBroker\Domain\Entry\Models\Groups\Member;
use PasswordBroker\Domain\Entry\Models\Groups\Moderator;
use PasswordBroker\Domain\Entry\Services\AddEntryGroup;
use PasswordBroker\Domain\Entry\Services\MoveEntryGroup;
use PasswordBroker\Domain\Entry\Services\UpdateEntryGroup;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryGroupValidationHandler;
use Symfony\Component\Mime\Encoder\Base64Encoder;

class EntryGroupController extends Controller
{
    public function __construct(private readonly EntryGroupService $entryGroupService)
    {
        $this->authorizeResource(EntryGroup::class, ['entryGroup']);
    }

    protected function resourceAbilityMap(): array
    {
        $resourceAbilityMap = parent::resourceAbilityMap();
        $resourceAbilityMap['move'] = 'move';
        $resourceAbilityMap['indexAsTree'] = 'viewAny';
        $resourceAbilityMap['update'] = 'update';

        return $resourceAbilityMap;
    }

    protected function resourceMethodsWithoutModels(): array
    {
        $resourceMethodsWithoutModels = parent::resourceMethodsWithoutModels();
        $resourceMethodsWithoutModels[] = 'indexAsTree';

        return $resourceMethodsWithoutModels;
    }

    #[Get(
        path     : "/passwordBroker/api/entryGroups",
        summary  : "List of EntryGroups that the User has access",
        tags     : ["PasswordBroker_EntryGroupController"],
        responses: [
            new Response(
                response   : 200,
                description: "List of EntryGroups",
                content    : new JsonContent(
                    type : "array",
                    items: new Items(ref: "#/components/schemas/PasswordBroker_EntryGroup"),
                ),
            ),
        ],
    )]
    public function index(): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        return new JsonResponse($user->userOf(), 200);
    }

    #[Get(
        path     : "/passwordBroker/api/entryGroupsWithFields",
        summary  : "List of EntryGroups with fields also with a tree of that Groups that the User has access",
        tags     : ["PasswordBroker_EntryGroupController"],
        responses: [
            new Response(
                response   : 200,
                description: "List of EntryGroups",
                content    : new JsonContent(
                    properties: [
                        new Property(property: "timestamp", type: "string", format: "date-time",),
                        new Property(
                            property  : "data",
                            properties: [
                                new Property(property: "groups", type: "array",
                                             items   : new Items(ref: "#/components/schemas/PasswordBroker_EntryGroup"),),
                                new Property(
                                    property: "trees",
                                    type    : "array",
                                    items   : new Items(
                                        properties: [
                                            new Property(property: "entry_group_id",
                                                         ref     : "#/components/schemas/PasswordBroker_EntryGroupId"),
                                            new Property(property: "title",
                                                         ref     : "#/components/schemas/PasswordBroker_GroupName"),
                                            new Property(property: "materialized_path",
                                                         ref     : "#/components/schemas/PasswordBroker_MaterializedPath"),
                                            new Property(property: "role", enum: [
                                                Admin::ROLE_NAME,
                                                Moderator::ROLE_NAME,
                                                Member::ROLE_NAME,
                                            ]),
                                            new Property(property   : "children",
                                                         description: "children with the same structure", type: "array",
                                                         items      : new Items()),
                                        ],
                                        type      : "object",
                                    ),
                                ),
                            ],
                            type      : "object"
                        ),
                    ],
                    type      : "object",
                ),
            ),
        ],
    )]
    public function indexWithFields(UserService $userService): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = Auth::user();
        $carbon = Carbon::now();
        /**
         * @var UserAccessToken $accessToken
         */
        $accessToken = $user->currentAccessToken();
        if ($accessToken) {
            $userApplication = $userService->getUserApplicationByToken($accessToken);
            if ($userApplication) {
                $this->dispatchSync(new UserApplicationChangeOfflineDatabaseRequiredUpdate(
                    userApplication                : $userApplication,
                    isOfflineDatabaseRequiredUpdate: new IsOfflineDatabaseRequiredUpdate(false),
                    carbon                         : $carbon
                ));
            }
        }

        return new JsonResponse([
            'timestamp' => $carbon->timestamp,
            'data' => [
                'groups' => $this->entryGroupService->groupsWithFields($user),
                'trees' => $this->entryGroupService->groupsAsTree($user->userOf()),
            ],
        ], 200);
    }

    #[Get(
        path     : "/passwordBroker/api/entryGroupsAsTree",
        summary  : "List of Groups as a tree of that EntryGroups that the User has access",
        tags     : ["PasswordBroker_EntryGroupController"],
        responses: [
            new Response(
                response   : 200,
                description: "Tree of EntryGroups",
                content    : new JsonContent(
                    properties: [
                        new Property(property: "timestamp", type: "string", format: "date-time",),
                        new Property(
                            property  : "data",
                            properties: [
                                new Property(
                                    property: "trees",
                                    type    : "array",
                                    items   : new Items(
                                        properties: [
                                            new Property(property: "entry_group_id",
                                                         ref     : "#/components/schemas/PasswordBroker_EntryGroupId"),
                                            new Property(property: "title",
                                                         ref     : "#/components/schemas/PasswordBroker_GroupName"),
                                            new Property(property: "materialized_path",
                                                         ref     : "#/components/schemas/PasswordBroker_MaterializedPath"),
                                            new Property(property: "role", enum: [
                                                Admin::ROLE_NAME,
                                                Moderator::ROLE_NAME,
                                                Member::ROLE_NAME,
                                            ]),
                                            new Property(property   : "children",
                                                         description: "children with the same structure", type: "array",
                                                         items      : new Items()),
                                        ],
                                        type      : "object",
                                    ),
                                ),
                            ],
                            type      : "object"
                        ),
                    ],
                    type      : "object",
                ),
            ),
        ],
    )]
    public function indexAsTree(): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        return new JsonResponse(
            [
                'trees' => $this->entryGroupService->groupsAsTree($user->userOf()),
            ]
            , 200);
    }

    #[Post(
        path       : "/passwordBroker/api/entryGroups",
        summary    : "List of Groups as a tree of that EntryGroups that the User has access",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema   : new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupRequest"),
            )
        ),
        tags       : ["PasswordBroker_EntryGroupController"],
        responses  : [
            new Response(
                response   : 200,
                description: "EntryGroup Was successfully created",
            ),
        ],
    )]
    public function store(EntryGroupRequest $request): JsonResponse
    {
        $entryGroup = EntryGroup::hydrate([$request->all()])->first();
        $entryGroup->exists = false;
        $response = $this->dispatchSync(
            new AddEntryGroup(
                $entryGroup,
                new EntryGroupValidationHandler()
            )
        );

        return new JsonResponse($response, 200);
    }

    #[Patch(
        path       : "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}",
        summary    : "Move an EntryGroup to other EntryGroup (change parent EntryGroup)",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema   : new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupMoveRequest"),
            )
        ),
        tags       : ["PasswordBroker_EntryGroupController"],
        parameters : [
            new PathParameter(name  : "entryGroup:entry_group_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
        ],
        responses  : [
            new Response(
                response   : 200,
                description: "EntryGroup Was successfully moved",
            ),
        ],
    )]
    public function move(EntryGroup $entryGroup, EntryGroupMoveRequest $request): JsonResponse
    {
        $this->dispatchSync(new MoveEntryGroup($entryGroup, $request->entryGroupTarget(), $this->entryGroupService));

        return new JsonResponse(1, 200);
    }

    /**
     * @param EntryGroup $entryGroup
     *
     * @return JsonResponse
     */
    #[Get(
        path      : "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}",
        summary   : "Get an EntryGroup",
        tags      : ["PasswordBroker_EntryGroupController"],
        parameters: [
            new PathParameter(name  : "entryGroup:entry_group_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
        ],
        responses : [
            new Response(
                response   : 200,
                description: "EntryGroup",
                content    : new JsonContent(
                    properties: [
                        new Property(
                            property: "entryGroup",
                            ref     : "#/components/schemas/PasswordBroker_EntryGroup"
                        ),
                        new Property(
                            property   : "role",
                            description: "auth user role in that group",
                            enum       : [Admin::ROLE_NAME, Moderator::ROLE_NAME, Member::ROLE_NAME]
                        ),
                    ],
                    type      : "object",
                ),
            ),
        ],
    )]
    public function show(EntryGroup $entryGroup): JsonResponse
    {
        $role = $entryGroup->users()->where('user_id', Auth::user()->user_id->getValue())->first();

        return new JsonResponse(
            [
                'entryGroup' => $entryGroup,
                'role' => $role,
            ]
            , 200);
    }

    #[Put(
        path       : "/entryGroups/{entryGroup:entry_group_id}",
        summary    : "",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema   : new Schema(ref: "#/components/schemas/PasswordBroker_GroupName"),
            )
        ),
        tags       : ["PasswordBroker_EntryGroupController"],
        parameters : [
            new PathParameter(name  : "entryGroup:entry_group_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
        ],
    )]
    public function update(EntryGroup $entryGroup, EntryGroupUpdateRequest $request): JsonResponse
    {
        $response = $this->dispatchSync(
            new UpdateEntryGroup(
                entryGroup                 : $entryGroup,
                name                       : $request->name,
                entryGroupValidationHandler: new EntryGroupValidationHandler()
            )
        );

        return new JsonResponse($response, 200);
    }

    #[Delete(
        path      : "/entryGroups/{entryGroup:entry_group_id}",
        summary   : "Remove an EntryGroup (mark it as deleted)",
        tags      : ["PasswordBroker_EntryGroupController"],
        parameters: [
            new PathParameter(name  : "entryGroup:entry_group_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
        ],
        responses : [
            new Response(
                response   : 200,
                description: "EntryGroup was deleted (marked as deleted)"
            ),
        ],
    )]
    public function destroy(EntryGroup $entryGroup): JsonResponse
    {
        $entryGroup->delete();

        return new JsonResponse([], 200);
    }
}
