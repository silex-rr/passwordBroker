<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes\AdditionalProperties;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\QueryParameter;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Http\Requests\EntrySearchRequest;
use PasswordBroker\Domain\Entry\Services\SearchEntry;


class EntrySearchController extends Controller
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    #[Get(
        path: "/passwordBroker/api/entrySearch",
        summary: "Get a List of users who belongs to that EntryGroup",
        tags: ["PasswordBroker_EntrySearchController"],
        parameters: [
            new QueryParameter(name: "q", required: false, schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntrySearchRequest_q"),),
            new QueryParameter(name: "page", required: false, schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntrySearchRequest_page"),),
            new QueryParameter(name: "perPage", required: false, schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntrySearchRequest_perPage"),),
        ],
        responses: [
            new Response(
                response: 200,
                description: "Founded Entries",
                content: new JsonContent(
                    allOf: [
                        new Schema(ref: "#/components/schemas/Common_Paginator",),
                        new Schema(
                            description: "Entry set as data type",
                            properties: [
                                new Property(
                                    property: "data",
                                    type: "array",
                                    items: new Items(
                                        allOf: [
                                            new Schema(ref: "#/components/schemas/PasswordBroker_Entry",),
                                            new AdditionalProperties(properties: [
                                                new Property(property: "entryGroup", ref: "#/components/schemas/PasswordBroker_EntryGroup",),
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
        ]
    )]
    public function index(EntrySearchRequest $request): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = Auth::user();
        $searchEntry = new SearchEntry(
            user: $user,
            query: $request->getQuery(),
            perPage: $request->getPerPage(),
            page: $request->getPage(),
        );

        return new JsonResponse($this->dispatchSync($searchEntry), 200);
    }
    //    public function index(UsersSearchRequest $request): JsonResponse
    //    {
    //
    //        $job = new SearchUsers(
    //            query: $request->getQuery(),
    //            perPage: $request->getPerPage(),
    //            page: $request->getPage(),
}
