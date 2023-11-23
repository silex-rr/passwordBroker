<?php

namespace System\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use System\Application\Http\Requests\BackupScheduleSettingRequest;
use System\Domain\Settings\Models\BackupScheduleSetting;
use System\Domain\Settings\Service\SetBackupScheduleSetting;

class BackupSettingController extends Controller
{
    use DispatchesJobs;
    public function show(BackupScheduleSetting $backupSetting): JsonResponse
    {
        return new JsonResponse($backupSetting, 200);
    }

    public function store(BackupScheduleSetting $backupSetting, BackupScheduleSettingRequest $request): JsonResponse
    {
        $this->dispatchSync(new SetBackupScheduleSetting($backupSetting, $request->schedule));
        return new JsonResponse($backupSetting, 200);
    }

    public function destroy(BackupScheduleSetting $backupSetting): JsonResponse
    {
        return new JsonResponse([], 200);
    }
}
