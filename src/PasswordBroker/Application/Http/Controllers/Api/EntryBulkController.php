<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\PathParameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Http\Requests\EntryBulkDestroyRequest;
use PasswordBroker\Application\Http\Requests\EntryBulkMoveRequest;
use PasswordBroker\Application\Services\EntryService;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Services\DestroyEntry;
use PasswordBroker\Domain\Entry\Services\MoveEntry;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class EntryBulkController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(private readonly EntryService $entryService)
    {
        $this->authorizeResource(EntryGroup::class, ['entryGroup']);
    }

    protected function resourceAbilityMap(): array
    {
        return [
            'bulkDestroy' => 'entryBulkDelete',
            'bulkMove' => 'entryBulkMove',
        ];
    }

    #[Post(
        path       : "/passwordBroker/api/entryGroups/{sourceEntryGroup:entry_group_id}/entries/bulkEdit/moveTo/{targetEntryGroup:entry_group_id}",
        summary    : "Delete list of Entries (mark as deleted)",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema   : new Schema(ref: "#/components/schemas/PasswordBroker_EntryBulkMoveRequest"),
            )
        ),
        tags       : ["PasswordBroker_EntryBulkController"],
        parameters : [
            new PathParameter(name  : "entryGroupSource:entry_group_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
            new PathParameter(name  : "entryGroupTarget:entry_group_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
        ],
        responses  : [
            new Response(
                response   : ResponseAlias::HTTP_NO_CONTENT,
                description: "Entries was successfully moved",
            ),
        ],
    )]
    public function bulkMove(
        EntryGroup           $entryGroup,
        EntryBulkMoveRequest $request,
    ): JsonResponse {

        /**
         * @var Entry[] $entries
         */
        $entries = Entry::whereIn('entry_id', $request->get('entries'))
            ->where('entry_group_id', $entryGroup->entry_group_id->getValue())->get();
        /**
         * @var User $user
         */
        $user = Auth::user();
        $entryGroupTarget = $request->entryGroupTarget();
        $master_password = $request->get('master_password');
        if (!$entryGroupTarget->admins()->where('user_id', $user->user_id->getValue())->exists()
            && !$entryGroupTarget->moderators()->where('user_id', $user->user_id->getValue())->exists()) {
            return new JsonResponse(['message' => 'You don`t have rights to the target group.'], ResponseAlias::HTTP_FORBIDDEN);
        }

        foreach ($entries as $entry) {
            $this->dispatchSync(new MoveEntry(
                    entry           : $entry,
                    entryGroupSource: $entryGroup,
                    entryGroupTarget: $entryGroupTarget,
                    master_password : $master_password
                )
            );
        }

        return new JsonResponse(null, ResponseAlias::HTTP_NO_CONTENT);
    }

    #[Post(
        path       : "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries/bulkEdit/delete",
        summary    : "Delete list of Entries (mark as deleted)",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema   : new Schema(ref: "#/components/schemas/PasswordBroker_EntryBulkDestroyRequest"),
            )
        ),
        tags       : ["PasswordBroker_EntryBulkController"],
        parameters : [
            new PathParameter(name  : "entryGroup:entry_group_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
        ],
        responses  : [
            new Response(
                response   : ResponseAlias::HTTP_NO_CONTENT,
                description: "Entries was successfully deleted (marked as deleted)",
            ),
        ],
    )]
    public function bulkDestroy(EntryGroup $entryGroup, EntryBulkDestroyRequest $request): JsonResponse
    {
        $entries = Entry::whereIn('entry_id', $request->get('entries'))
            ->where('entry_group_id', $entryGroup->entry_group_id->getValue())->get();

        foreach ($entries as $entry) {
            $this->dispatchSync(new DestroyEntry(
                entry     : $entry,
                entryGroup: $entryGroup
            ));
        }

        return new JsonResponse(null, ResponseAlias::HTTP_NO_CONTENT);
    }
}
