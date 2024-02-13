<?php

namespace Identity\Domain\User\Services;

use Identity\Domain\User\Events\EmailForUserWasChanged;
use Identity\Domain\User\Events\PasswordForUserWasChanged;
use Identity\Domain\User\Models\Attributes\Email;
use Identity\Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Hash;


class ChangeEmailForUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private User $userTarget,
        private string $newEmail
    )
    {}

    public function handle(): void
    {
        $this->userTarget->email = Email::fromNative($this->newEmail);
        $this->userTarget->save();
        event(new EmailForUserWasChanged(user: $this->userTarget));
    }
}
