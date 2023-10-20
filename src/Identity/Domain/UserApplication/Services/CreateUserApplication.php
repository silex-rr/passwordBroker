<?php

namespace Identity\Domain\UserApplication\Services;

use Identity\Domain\User\Models\User;
use Identity\Domain\UserApplication\Events\UserApplicationWasCreated;
use Identity\Domain\UserApplication\Models\Attributes\ClientId;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseMode;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseRequiredUpdate;
use Identity\Domain\UserApplication\Models\Attributes\IsRsaPrivateRequiredUpdate;
use Identity\Domain\UserApplication\Models\Attributes\UserApplicationId;
use Identity\Domain\UserApplication\Models\UserApplication;
use Illuminate\Foundation\Events\Dispatchable;

class CreateUserApplication
{
    use Dispatchable;

    public function __construct(
        private readonly User     $user,
        private readonly ClientId $clientId
    )
    {
    }

    public function handle(): ?UserApplication
    {
        $userApplication = new UserApplication();
        $userApplication->user_application_id = new UserApplicationId();
        $userApplication->user()->associate($this->user);
        $userApplication->client_id = $this->clientId;
        $userApplication->is_offline_database_mode = IsOfflineDatabaseMode::fromNative(false);
        $userApplication->is_rsa_private_required_update = IsRsaPrivateRequiredUpdate::fromNative(false);
        $userApplication->is_offline_database_required_update = IsOfflineDatabaseRequiredUpdate::fromNative(false);
        $userApplication->save();
        event(new UserApplicationWasCreated($userApplication));
        return $userApplication;
    }
}
