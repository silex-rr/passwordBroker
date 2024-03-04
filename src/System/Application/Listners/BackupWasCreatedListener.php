<?php

namespace System\Application\Listners;

use Illuminate\Contracts\Bus\Dispatcher;
use System\Application\Services\BackupService;
use System\Domain\Backup\Events\BackupWasCreated;
use System\Domain\Backup\Models\Attributes\BackupState;
use System\Domain\Backup\Service\MakeBackup;

class BackupWasCreatedListener
{

    public function handle(BackupWasCreated $backupWasCreated): void
    {
        /**
         * @var Dispatcher $dispatcher
         */
        $dispatcher = app(Dispatcher::class);
        /**
         * @var BackupService $backupService
         */
        $backupService = app(BackupService::class);

        if (!$backupWasCreated->doNotMakeBackup) {
            $dispatcher->dispatch(new MakeBackup(
                backup: $backupWasCreated->backup,
                backupService: $backupService
            ));
        }


    }
}
