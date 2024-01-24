<?php

namespace System\Infrastructure\Repository;

use App\Common\Domain\Abstractions\BaseRepository;
use System\Domain\Backup\Models\Backup;

class BackupRepository extends BaseRepository
{

    /**
     * @inheritDoc
     */
    public function model(): string
    {
        return Backup::class;
    }
}
