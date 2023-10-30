<?php

namespace Identity\Application\Services;

use Identity\Domain\User\Models\UserAccessToken;
use Identity\Domain\UserApplication\Models\UserApplication;

class UserService
{
    public function getUserApplicationByToken(UserAccessToken $token): ?UserApplication
    {
        return UserApplication::where('client_id', $token->name)->first();
    }
}
