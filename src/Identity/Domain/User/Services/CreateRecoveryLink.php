<?php

namespace Identity\Domain\User\Services;

use Identity\Domain\User\Events\UserRecoveryLinkWasCreated;
use Identity\Domain\User\Models\RecoveryLink;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateRecoveryLink implements ShouldQueue
{
    use Batchable, Dispatchable;

    public function __construct(
        private RecoveryLink $recoveryLink
    )
    {
    }

    public function handle()
    {
        $this->recoveryLink->save();
        event(new UserRecoveryLinkWasCreated($this->recoveryLink));
    }
}
