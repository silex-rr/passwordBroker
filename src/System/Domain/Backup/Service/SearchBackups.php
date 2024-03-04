<?php

namespace System\Domain\Backup\Service;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use System\Infrastructure\Criteria\CriteriaBackupFileNameContains;
use System\Infrastructure\Order\BackupOrder;
use System\Infrastructure\Repository\BackupRepository;

class SearchBackups implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    public function __construct(
        protected string $query,
        protected int $perPage,
        protected int $page
    )
    {
    }

    public function handle(): Paginator
    {
        $backupRepository = new BackupRepository(app());
        if (!empty($this->query)) {
            $backupRepository->pushCriteria(new CriteriaBackupFileNameContains($this->query));
        }
        $backupOrder = new BackupOrder();
        $backupOrder->desc('created_at');
        $backupRepository->pushOrder($backupOrder);
        return $backupRepository->paginate(perPage: $this->perPage, columns: ['*'], pageName: 'page', page: $this->page);

    }

}
