<?php

namespace Identity\Domain\User\Services;

use Identity\Domain\User\Events\PasswordForUserWasChanged;
use Identity\Domain\User\Events\UserWasDestroyed;
use Identity\Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Hash;


class ChangePasswordForUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private User $userTarget,
        private string $newPassword
    )
    {}

    public function handle(): void
    {
        $this->userTarget->password = Hash::make($this->newPassword);
        $this->userTarget->save();
        event(new PasswordForUserWasChanged(user: $this->userTarget));
    }
}
