<?php

namespace Identity\Domain\User\Services;

use Identity\Domain\User\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetUserToken implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    public function __construct(
        protected User $user,
        protected string $token_name,
    )
    {
    }

    public function handle()
    {
       $this->user->tokens()
           ->where('name', $this->token_name)
           ->delete();

        return $this->user->createToken($this->token_name)->plainTextToken;
    }
}
