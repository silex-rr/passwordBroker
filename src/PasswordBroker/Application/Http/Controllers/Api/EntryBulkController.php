<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Patch;
use OpenApi\Attributes\PathParameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Http\Requests\EntryBulkDestroyRequest;
use PasswordBroker\Application\Http\Requests\EntryMoveRequest;
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
            'destroy' => 'entryBulkDelete'
        ];
    }

    #[Patch(
        path: "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}",
        summary: "Move an Entry to another EntryGroup",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryMoveRequest"),
            )
        ),
        tags: ["PasswordBroker_EntryController"],
        parameters: [
            new PathParameter(name: "entryGroup:entry_group_id", required: true, schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
            new PathParameter(name: "entry:entry_id", required: true, schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryId")),
        ],
        responses: [
            new Response(
                response: 200,
                description: "Entry was successfully moved to the target EntryGroup",
            )
        ],
    )]
    public function move(EntryGroup $entryGroupSource, Entry $entry, EntryMoveRequest $entryMoveRequest): JsonResponse
    {
        $response = $this->dispatchSync(new MoveEntry(
                entry: $entry,
                entryGroupSource: $entryGroupSource,
                entryGroupTarget: $entryMoveRequest->entryGroupTarget(),
                master_password: $entryMoveRequest->get('master_password')
            )
        );

        return new JsonResponse($response, 200);
    }

    #[Post(
        path       : "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries/bulkEdit/delete",
        summary    : "Delete list of Entries (mark as deleted)",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryBulkDestroyRequest"),
            )
        ),
        tags       : ["PasswordBroker_EntryBulkController"],
        parameters : [
            new PathParameter(name: "entryGroup:entry_group_id", required: true, schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
        ],
        responses  : [
            new Response(
                response: ResponseAlias::HTTP_NO_CONTENT,
                description: "Entries was successfully deleted (marked as deleted)",
            )
        ],
    )]
    public function destroy(EntryGroup $entryGroup, EntryBulkDestroyRequest $request): JsonResponse
    {
        $entries = Entry::whereIn('entry_id', $request->get('entries'))
            ->where('entry_group_id', $entryGroup->entry_group_id->getValue())->get();

        foreach ($entries as $entry) {
            $this->dispatchSync(new DestroyEntry(
                entry: $entry,
                entryGroup: $entryGroup
            ));
        }

        return new JsonResponse(null, ResponseAlias::HTTP_NO_CONTENT);
    }
}
