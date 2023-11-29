<?php

namespace System\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use System\Application\Services\BackupService;
use System\Domain\Backup\Models\Backup;
use System\Domain\Backup\Service\CreateBackup;

class BackupController extends Controller
{
    use DispatchesJobs;
    public function store(BackupService $backupService): JsonResponse
    {
        $backup = $this->dispatchSync(new CreateBackup(backup: new Backup(), backupService: $backupService));
        return new JsonResponse($backup, 200);
    }

    public function show(Backup $backup): JsonResponse
    {
        return new JsonResponse($backup, 200);
    }
}
