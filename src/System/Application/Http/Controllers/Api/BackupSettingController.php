<?php

namespace System\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use System\Application\Http\Requests\BackupSettingRequest;
use System\Domain\Settings\Models\BackupSetting;
use System\Domain\Settings\Service\SetBackupScheduleSetting;

class BackupSettingController extends Controller
{
    use DispatchesJobs;
    public function show(BackupSetting $backupSetting): JsonResponse
    {
        return new JsonResponse($backupSetting, 200);
    }

    public function store(BackupSetting $backupSetting, BackupSettingRequest $request): JsonResponse
    {
        $this->dispatchSync(new SetBackupScheduleSetting($backupSetting, $request->schedule));
        return new JsonResponse($backupSetting, 200);
    }

    public function destroy(BackupSetting $backupSetting): JsonResponse
    {
        return new JsonResponse([], 200);
    }
}
