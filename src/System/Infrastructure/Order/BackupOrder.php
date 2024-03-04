<?php

namespace System\Infrastructure\Order;

use App\Common\Domain\Abstractions\OrderBase;
use Illuminate\Database\Eloquent\Model;
use System\Domain\Backup\Models\Backup;

class BackupOrder extends OrderBase
{
    #[\Override] public function getModel(): ?Model
    {
        return app(Backup::class);
    }

}
