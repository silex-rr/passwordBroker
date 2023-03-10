<?php

namespace Identity\Domain\User\Services;

use Identity\Domain\User\Events\UserWasDestroyed;
use Identity\Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class DestroyUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
      private User $userTarget
    )
    {}

    public function handle(): void
    {
        $this->userTarget->delete();
        event(new UserWasDestroyed(userTarget: $this->userTarget));
    }
}
