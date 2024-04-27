<?php

namespace Identity\Application\Services;

use Identity\Domain\User\Models\User;
use Identity\Domain\User\Models\UserAccessToken;
use Identity\Domain\UserApplication\Models\UserApplication;
use RuntimeException;

class UserService
{
    public const string USER_TEMPORARY = 'user_';
    public const int MAX_ATTEMPTS = 30;

    public function getUserApplicationByToken(UserAccessToken $token): ?UserApplication
    {
        return UserApplication::where('client_id', $token->name)->first();
    }

    public function getUserUniqTemporaryName(): string
    {
        $name = self::USER_TEMPORARY . rand(1000, 9999);
        $i = 0;
        do {
            if (++$i > self::MAX_ATTEMPTS) {
                throw new RuntimeException("Max attempts of getting uniq user name reached");
            }
            if ($i % 10 === 0) {
                $name = self::USER_TEMPORARY . rand(1000, 9999);
            }
            $name .= rand(0, 9);
        } while (User::where('name', $name)->exists());

        return $name;
    }
}
