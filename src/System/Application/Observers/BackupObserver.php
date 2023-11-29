<?php

namespace System\Application\Observers;

use System\Domain\Backup\Models\Attributes\BackupState;
use System\Domain\Backup\Models\Attributes\FileName;
use System\Domain\Backup\Models\Backup;

class BackupObserver
{
    public function creating(Backup $backup): void
    {
        $backup->backup_id;
        $backup->state = BackupState::AWAIT;
    }

}
