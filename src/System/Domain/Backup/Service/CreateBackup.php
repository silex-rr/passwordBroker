<?php

namespace System\Domain\Backup\Service;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use System\Application\Services\BackupService;
use System\Domain\Backup\Events\BackupWasCreated;
use System\Domain\Backup\Models\Backup;

class CreateBackup implements ShouldQueue
{
    use Batchable, Dispatchable;

    public function __construct(
        private readonly Backup        $backup,
        private readonly BackupService $backupService,
        private readonly bool          $doNotMakeBackup = false,
    )
    {}
    public function handle(): Backup
    {
//        $this->backupService->makeBackup($this->backup);
        $this->backup->save();
        event(new BackupWasCreated($this->backup, $this->doNotMakeBackup));
        return $this->backup;
    }
}
