<?php

namespace Identity\Domain\UserApplication\Services;

use Identity\Domain\User\Models\UserAccessToken;
use Identity\Domain\UserApplication\Events\UserApplicationOfflineDatabaseModeHasChanged;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseMode;
use Identity\Domain\UserApplication\Models\UserApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class UpdateOfflineDatabaseMode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        private readonly UserApplication $userApplication,
        private readonly IsOfflineDatabaseMode $isOfflineDatabaseMode
    ){}

    public function handle(): void
    {
        if ($this->userApplication->is_offline_database_mode->equals($this->isOfflineDatabaseMode)) {
            return;
        }
        $this->userApplication->is_offline_database_mode = $this->isOfflineDatabaseMode;
        $this->userApplication->save();
        event(new UserApplicationOfflineDatabaseModeHasChanged($this->userApplication, $this->isOfflineDatabaseMode));
    }

}
