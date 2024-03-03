<?php

namespace System\Application\Services;

trait BackupTemporaryDir
{
    public const string BACKUP_TEMPORARY_LOCATION = 'backup-temp';

    /**
     * @return string
     */
    public function getTempDirForBackup(): string
    {
        return config('filesystems.disks.local.root') . DIRECTORY_SEPARATOR . self::BACKUP_TEMPORARY_LOCATION;
    }
}
