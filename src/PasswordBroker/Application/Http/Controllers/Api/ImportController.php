<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Bus\Dispatcher;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Http\Requests\ImportRequest;
use PasswordBroker\Domain\Entry\Services\ImportKeePassXML;

class ImportController extends Controller
{
    #[Post(
        path: "/passwordBroker/api/import",
        summary: "Import Data from other sources [KeePass]",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(
                    ref: "#/components/schemas/PasswordBroker_ImportRequest",
                ),
            ),
        ),
        tags: ["PasswordBroker_ImportController"],
        responses: [
            new Response(
                response: 200,
                description: "Data successfully imported from a File",
            ),
        ]
    )]
    public function store(ImportRequest $request): JsonResponse
    {
        $this->dispatchSync(
            new ImportKeePassXML(
                filePath: $request->file('file')?->getRealPath(),
                masterPassword: $request->get('master_password'),
                dispatcher: app(Dispatcher::class)
            )
        );

        return new JsonResponse(null, 200);
    }
}
