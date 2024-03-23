<?php

namespace System\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use System\Application\Http\Requests\BackupSettingRequest;
use System\Domain\Settings\Models\BackupSetting;
use System\Domain\Settings\Service\SetBackupSetting;

class BackupSettingController extends Controller
{
    use DispatchesJobs;

    /**
     * Show the backup setting.
     *
     * @param BackupSetting $backupSetting The backup setting object to be shown.
     *
     * @return JsonResponse The JSON response containing the backup setting.
     */
    #[Get(
        path: "/system/api/setting/backupSetting/backup",
        summary: "Return current backup settings",
        tags: ["System_BackupSettingController"],
        responses: [
            new Response(
                response: 200,
                description: "Backup Settings",
                content: new JsonContent(
                    ref: "#/components/schemas/System_BackupSetting",
                ),
            ),
        ],
    )]
    public function show(BackupSetting $backupSetting): JsonResponse
    {
        return new JsonResponse($backupSetting, 200);
    }

    #[Post(
        path: "/system/api/setting/backupSetting/backup",
        summary: "Update Backup Settings",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(ref: "#/components/schemas/System_BackupSetting",),
            )
        ),
        tags: ["System_BackupSettingController"],
        responses: [
            new Response(
                response: 200,
                description: "Backup Settings was successfully updated",
                content: new JsonContent(
                    ref: "#/components/schemas/System_BackupSetting",
                ),
            ),
        ]
    )]
    public function store(BackupSetting $backupSetting, BackupSettingRequest $request): JsonResponse
    {
        $this->dispatchSync(new SetBackupSetting(
            backupSetting: $backupSetting,
            scheduleArray: $request->schedule,
            enable: $request->enable,
            email_enable: $request->email_enable,
            email: $request->email,
            archive_password: $request->archive_password,
        ));
        return new JsonResponse($backupSetting, 200);
    }

    public function destroy(BackupSetting $backupSetting): JsonResponse
    {
        return new JsonResponse([], 200);
    }
}
