<?php

namespace System\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use System\Application\Http\Requests\InitialRecoveryRequest;
use System\Application\Services\RecoveryService;

class RecoveryController extends Controller
{
    public function performRecovery(InitialRecoveryRequest $request, RecoveryService $recoveryService): JsonResponse
    {
        $backupFile = $request->file('backupFile');
        $recoveryService->recoveryFromBackupFile($backupFile, $request->password);
        return new JsonResponse(['status' => 'done'], 200);
    }
}
