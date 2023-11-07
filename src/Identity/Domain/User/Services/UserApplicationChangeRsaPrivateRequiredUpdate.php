<?php

namespace Identity\Domain\User\Services;

use Carbon\Carbon;
use Identity\Domain\User\Events\UserApplicationRsaPrivateRequiredUpdateChanged;
use Identity\Domain\UserApplication\Models\Attributes\IsRsaPrivateRequiredUpdate;
use Identity\Domain\UserApplication\Models\UserApplication;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class UserApplicationChangeRsaPrivateRequiredUpdate implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected UserApplication $userApplication,
        protected readonly IsRsaPrivateRequiredUpdate $isRsaPrivateRequiredUpdate,
        protected readonly ?Carbon $carbon = null
    )
    {
    }

    public function handle(): void
    {
        $this->userApplication->is_rsa_private_required_update = $this->isRsaPrivateRequiredUpdate;
        if ($this->isRsaPrivateRequiredUpdate->getValue() === false
            && $this->carbon
        ) {
            $this->userApplication->rsa_private_fetched_at = $this->carbon;
        }
        $this->userApplication->save();
        event(new UserApplicationRsaPrivateRequiredUpdateChanged($this->userApplication));
    }
}
