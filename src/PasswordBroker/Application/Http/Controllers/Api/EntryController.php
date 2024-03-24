<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes\Delete;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Patch;
use OpenApi\Attributes\PathParameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Put;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Http\Requests\EntryMoveRequest;
use PasswordBroker\Application\Http\Requests\EntryRequest;
use PasswordBroker\Application\Services\EntryService;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Services\AddEntry;
use PasswordBroker\Domain\Entry\Services\DestroyEntry;
use PasswordBroker\Domain\Entry\Services\MoveEntry;
use PasswordBroker\Domain\Entry\Services\UpdateEntry;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryValidationHandler;

class EntryController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(private readonly EntryService $entryService)
    {
        $this->authorizeResource(Entry::class, ['entry']);
    }

    #[Get(
        path: "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries",
        summary: "Get a list of Entries that belong to EntryGroup",
        tags: ["PasswordBroker_EntryController"],
        parameters: [
            new PathParameter(parameter: "{entryGroup:entry_group_id}", ref: "#/components/schemas/PasswordBroker_EntryGroupId"),
        ],
        responses: [
            new Response(
                response: 200,
                description: "List of Entries that belong to selected EntryGroup",
                content: new JsonContent(
                    type: "array",
                    items: new Items(
                        ref: "#/components/schemas/PasswordBroker_Entry",
                    ),
                ),
            ),
        ],
    )]
    public function index(EntryGroup $entryGroup): JsonResponse
    {
        return new JsonResponse($entryGroup->entries()->get(), 200);
    }

    #[Post(
        path: "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries",
        summary: "Create new Entry in selected EntryGroup",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryRequest"),
            )
        ),
        tags: ["PasswordBroker_EntryController"],
        parameters: [
            new PathParameter(parameter: "{entryGroup:entry_group_id}", ref: "#/components/schemas/PasswordBroker_EntryGroupId"),
        ],
        responses: [
            new Response(
                response: 200,
                description: "Entry was successfully created in selected EntryGroup",
            ),
        ],
    )]
    public function store(EntryRequest $request): JsonResponse
    {

        /**
         * @var Entry $entry
         */
        $entry = Entry::hydrate([$request->all()])->first();
        $entry->exists = false;

        $response = $this->dispatchSync(new AddEntry($entry, $request->entryGroup, new EntryValidationHandler()));

        return new JsonResponse($response, 200);
    }

    public function show(): JsonResponse
    {
        return new JsonResponse(['data'], 200);
    }


    #[Put(
        path: "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}",
        summary: "Update an Entry",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryRequest"),
            )
        ),
        tags: ["PasswordBroker_EntryController"],
        parameters: [
            new PathParameter(parameter: "{entryGroup:entry_group_id}", ref: "#/components/schemas/PasswordBroker_EntryGroupId"),
            new PathParameter(parameter: "{entry:entry_id}", ref: "#/components/schemas/PasswordBroker_EntryId"),
        ],
        responses: [
            new Response(
                response: 200,
                description: "Entry was successfully updated",
            )
        ],
    )]
    public function update(EntryGroup $entryGroup, Entry $entry, EntryRequest $request): JsonResponse
    {
        $response = $this->dispatchSync(new UpdateEntry(
                entry: $entry,
                entryGroup: $entryGroup,
                attributes: $request->allWithCasts(),
                entryValidationHandler: new EntryValidationHandler())
        );
        return new JsonResponse($response, 200);
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
            new PathParameter(parameter: "{entryGroup:entry_group_id}", ref: "#/components/schemas/PasswordBroker_EntryGroupId"),
            new PathParameter(parameter: "{entry:entry_id}", ref: "#/components/schemas/PasswordBroker_EntryId"),
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

    #[Delete(
        path: "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}",
        summary: "Delete an Entry (mark as deleted)",
        tags: ["PasswordBroker_EntryController"],
        parameters: [
            new PathParameter(parameter: "{entryGroup:entry_group_id}", ref: "#/components/schemas/PasswordBroker_EntryGroupId"),
            new PathParameter(parameter: "{entry:entry_id}", ref: "#/components/schemas/PasswordBroker_EntryId"),
        ],
        responses: [
            new Response(
                response: 200,
                description: "Entry was successfully deleted (marked as deleted)",
            )
        ],
    )]
    public function destroy(EntryGroup $entryGroup, Entry $entry): JsonResponse
    {
        $response = $this->dispatchSync(new DestroyEntry(
            entry: $entry,
            entryGroup: $entryGroup
        ));

        return new JsonResponse($response, 200);
    }

    protected function resourceAbilityMap(): array
    {
        $resourceAbilityMap = parent::resourceAbilityMap();
        $resourceAbilityMap['move'] = 'move';
        return $resourceAbilityMap;
    }
}
