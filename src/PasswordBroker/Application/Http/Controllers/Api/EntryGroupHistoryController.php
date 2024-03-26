<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes\AdditionalProperties;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\PathParameter;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\QueryParameter;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Http\Requests\EntryFieldHistorySearchRequest;
use PasswordBroker\Application\Services\SearchEntryFieldHistory;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\EntryFieldHistory;

class EntryGroupHistoryController extends Controller
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    #[Get(
        path: "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/history",
        summary: "Get an EntryGroup history",
        tags: ["PasswordBroker_EntryGroupHistoryController"],
        parameters: [
            new PathParameter(name: "entryGroup:entry_group_id", required: true, schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
        ],
        responses: [
            new Response(
                response: 200,
                description: "History successfully received",
                content: new JsonContent(
                    type: "array",
                    items: new Items(
                        allOf: [
                            new Schema(ref: "#/components/schemas/PasswordBroker_EntryFieldHistory",),
                            new AdditionalProperties(
                                properties: [
                                    new Property(
                                        property: "field",
                                        allOf: [
                                            new Schema(ref: "#/components/schemas/PasswordBroker_Field",),
                                            new AdditionalProperties(
                                                properties: [
                                                    new Property(property: "entry", ref: "#/components/schemas/PasswordBroker_Entry",)
                                                ],
                                            ),
                                        ],
                                    ),
                                    new Property(
                                        property: "user",
                                        ref: "#/components/schemas/Identity_User",
                                    ),
                                ],
                            ),
                        ],
                    ),
                ),
            ),
        ]
    )]
    public function index(EntryGroup $entryGroup): JsonResponse
    {
        return new JsonResponse(EntryFieldHistory::with(['field.entry', 'user'])->belongToEntryGroup($entryGroup)->get());
    }

    #[Get(
        path: "/passwordBroker/api/entryGroup/history",
        summary: "Search in EntryGroup history",
        tags: ["PasswordBroker_EntryGroupHistoryController"],
        parameters: [
            new QueryParameter(name: "q", required: false, schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryFieldHistorySearchRequest_q"),),
            new QueryParameter(name: "page", required: false, schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryFieldHistorySearchRequest_page"),),
            new QueryParameter(name: "perPage", required: false, schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryFieldHistorySearchRequest_perPage"),),
            new QueryParameter(name: "entryGroupInclude", required: false, schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
            new QueryParameter(name: "entryGroupExclude", required: false, schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
        ],
        responses: [
            new Response(
                response: 200,
                description: "Search result",
                content: new JsonContent(
                    allOf: [
                        new Schema(ref: "#/components/schemas/Common_Paginator",),
                        new Schema(
                            description: "User set as data type",
                            properties: [
                                new Property(
                                    property: "data",
                                    type: "array",
                                    items: new Items(
                                        allOf: [
                                            new Schema(ref: "#/components/schemas/PasswordBroker_EntryFieldHistory",),
                                            new AdditionalProperties(properties: [
                                                new Property(property: "entry", ref: "#/components/schemas/PasswordBroker_Entry",),
                                            ]),
                                        ],
                                    )
                                )
                            ],
                            type: "object",
                        )
                    ]
                ),
            )
        ],
    )]
    public function search(EntryFieldHistorySearchRequest $request): JsonResponse
    {
        $job = new SearchEntryFieldHistory(
            query: $request->getQuery(),
            perPage: $request->getPerPage(),
            page: $request->getPage(),
            entryGroupInclude: $request->getEntryGroupInclude(),
            entryGroupExclude: $request->getEntryGroupExclude()
        );

        return new JsonResponse($this->dispatchSync($job), 200);
    }

    protected function resourceAbilityMap(): array
    {
        $resourceAbilityMap = parent::resourceAbilityMap();
        $resourceAbilityMap['search'] = 'search';
        return $resourceAbilityMap;
    }
}
