<?php

namespace Identity\Domain\User\Services;

use Carbon\Carbon;
use Identity\Domain\User\Events\UserApplicationOfflineDatabaseRequiredUpdateChanged;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseRequiredUpdate;
use Identity\Domain\UserApplication\Models\UserApplication;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class UserApplicationChangeOfflineDatabaseRequiredUpdate implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected UserApplication $userApplication,
        protected readonly IsOfflineDatabaseRequiredUpdate $isOfflineDatabaseRequiredUpdate,
        protected readonly ?Carbon $carbon = null
    )
    {
    }

    public function handle(): void
    {
        $this->userApplication->is_offline_database_required_update = $this->isOfflineDatabaseRequiredUpdate;
        if ($this->isOfflineDatabaseRequiredUpdate->getValue() === false
            && $this->carbon
        ) {
            $this->userApplication->offline_database_fetched_at = $this->carbon;
        }
        $this->userApplication->save();
        event(new UserApplicationOfflineDatabaseRequiredUpdateChanged($this->userApplication));
    }
}
