<?php

namespace System\Application\Services;

use RuntimeException;

trait BackupTemporaryDir
{
    public const string BACKUP_TEMPORARY_LOCATION = 'backup-temp';

    /**
     * @return string
     */
    public function getTempDirForBackup(): string
    {
        $tmpDirForBackup = config('filesystems.disks.local.root') . DIRECTORY_SEPARATOR . self::BACKUP_TEMPORARY_LOCATION;

        if (is_dir($tmpDirForBackup)) {
            return $tmpDirForBackup;
        }

        if (!mkdir($tmpDirForBackup, 0777, true) && !is_dir($tmpDirForBackup)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $tmpDirForBackup));
        }

        return $tmpDirForBackup;
    }
}
