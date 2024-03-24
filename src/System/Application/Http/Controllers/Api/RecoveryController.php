<?php

namespace System\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use System\Application\Http\Requests\InitialRecoveryRequest;
use System\Application\Services\RecoveryService;

class RecoveryController extends Controller
{
    #[Post(
        path: "/system/api/recovery",
        summary: "Recovery system from a Backup",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(
                    properties: [
                        new Property(
                            property: "backupFile",
                            description: "a ZIP file with backup",
                            type: "string",
                            format: "binary"
                        ),
                    ],
                ),
            ),
        ),
        tags: ["System_RecoveryController"],
        responses: [
            new Response(
                response: 200,
                description: "System successfully recovered from backup",
                content: new JsonContent(
                    properties: [
                        new Property(property: "status", type: "string", default: "done")
                    ],
                ),
            ),
        ]
    )]
    public function performRecovery(InitialRecoveryRequest $request, RecoveryService $recoveryService): JsonResponse
    {
        $backupFile = $request->file('backupFile');
        $recoveryService->recoveryFromBackupFile($backupFile, $request->password);
        return new JsonResponse(['status' => 'done'], 200);
    }
}
