<?php

namespace System\Domain\Backup\Service;

use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use System\Application\Services\BackupService;
use System\Domain\Backup\Events\BackupFailed;
use System\Domain\Backup\Events\BackupWasMade;
use System\Domain\Backup\Models\Attributes\BackupCreated;
use System\Domain\Backup\Models\Attributes\BackupState;
use System\Domain\Backup\Models\Attributes\ErrorMessage;
use System\Domain\Backup\Models\Backup;

class MakeBackup implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        private readonly Backup        $backup,
        private readonly BackupService $backupService,
    )
    {}
    public function handle(): void
    {
        if ($this->backup->state !== BackupState::AWAIT) {
            return;
        }
        $this->backup->state = BackupState::CREATING;
        try {
            $this->backupService->makeBackup($this->backup);
            $this->backup->state = BackupState::CREATED;
            $this->backup->backup_created = new BackupCreated(Carbon::now());
            event(new BackupWasMade($this->backup));
        } catch (Exception $e) {
            $this->backup->state = BackupState::ERROR;
            $this->backup->error_message = new ErrorMessage($e->getMessage());
            event(new BackupFailed($this->backup, $e->getMessage()));
        }
        $this->backup->save();
    }
}
