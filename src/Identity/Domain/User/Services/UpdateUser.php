<?php

namespace Identity\Domain\User\Services;

use Identity\Domain\User\Events\UserWasUpdated;
use Identity\Domain\User\Models\Attributes\Email;
use Identity\Domain\User\Models\Attributes\UserName;
use Identity\Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Hash;

class UpdateUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly User $userTarget,
        private readonly ?string $username = null,
        private readonly ?string $email = null,
        private readonly ?string $password = null
    )
    {}

    public function handle(): void
    {
        if (!empty($this->username)) {
            $this->userTarget->name = new UserName($this->username);
        }
        if (!empty($this->email)) {
            $this->userTarget->email = new Email($this->email);
        }
        if (!empty($this->password)) {
            $this->userTarget->password = Hash::make($this->password);
        }
        $this->userTarget->save();
        event(new UserWasUpdated(userTarget: $this->userTarget));
    }
}
