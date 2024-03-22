<?php

namespace System\Application\Http\Controllers\Api;

use App\Common\Infrastracture\Attributes\PaginatorAttribute;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\PathParameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\QueryParameter;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use System\Application\Http\Requests\BackupSearchRequest;
use System\Application\Services\BackupService;
use System\Domain\Backup\Models\Backup;
use System\Domain\Backup\Service\CreateBackup;
use System\Domain\Backup\Service\SearchBackups;

class BackupController extends Controller
{
    use DispatchesJobs;

    /**
     * @param BackupSearchRequest $request
     * @return JsonResponse<PaginatorAttribute>
     */
    #[Get(
        path: "/system/api/backups",
        summary: "Backup entities list",
        tags: ["System_BackupController"],
        parameters: [
            new QueryParameter(
                name: "q",
                required: false,
                schema: new Schema(ref: "#/components/schemas/System_BackupSearchRequest_q")
            ),
            new QueryParameter(
                name: "perPage",
                required: false,
                schema: new Schema(ref: "#/components/schemas/System_BackupSearchRequest_perPage"),
            ),
            new QueryParameter(
                name: "page",
                required: false,
                schema: new Schema(ref: "#/components/schemas/System_BackupSearchRequest_page"),
            ),
        ],
        responses: [
            new Response(
                response: 200,
                description: "List of backups with pagination",
                content: new JsonContent(
                    allOf: [
                        new Schema(ref: "#/components/schemas/Common_Paginator",),
                        new Schema(
                            description: "Backup set as data type", properties: [
                                new Property(property: "data", type: "array",items: new Items(ref: "#/components/schemas/System_Backup"))
                            ],
                            type: "object",
                        )
                    ]
                )
            ),
        ],
    )]
    public function index(BackupSearchRequest $request): JsonResponse
    {
        return new JsonResponse($this->dispatchSync(new SearchBackups(
            query: $request->getQuery(), perPage: $request->getPerPage(), page: $request->getPage()
        )));
    }

    #[Post(
        path: "/system/api/backups",
        summary: "Create new Backup",
        tags: ["System_BackupController"],
        responses: [
            new Response(response: 200, description: "Backup entity was successfully created and put in the queue for executing"),
        ],
    )]
    public function store(BackupService $backupService): JsonResponse
    {
        $backup = $this->dispatchSync(new CreateBackup(backup: new Backup(), backupService: $backupService));
        return new JsonResponse($backup, 200);
    }

    #[Get(
        path: "/backups/{backup:backup_id}",
        summary: "Show a Backup entity",
        tags: ["System_BackupController"],
        parameters: [
            new PathParameter(
                name: "backup:backup_id",
                required: true,
                schema: new Schema(ref: "#/components/schemas/System_BackupId")
            ),
        ],
        responses: [
            new Response(
                ref: "#/components/schemas/System_Backup",
                response: 200,
                description: "Backup entity",
            ),
        ],
    )]
    public function show(Backup $backup): JsonResponse
    {
        return new JsonResponse($backup, 200);
    }
}
