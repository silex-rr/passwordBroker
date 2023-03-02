<?php

namespace Identity\Domain\User\Models;

use PasswordBroker\Domain\Entry\Models\Groups\Admin;
use PasswordBroker\Domain\Entry\Models\Groups\Member;
use PasswordBroker\Domain\Entry\Models\Groups\Moderator;

class UserToGroupTranslator
{
    public function toMember(User $user): Member
    {
        return new Member($user->email, $user->user_id, $user->username);
    }

    public function toModerator(User $user): Moderator
    {
        return new Moderator($user->email, $user->user_id, $user->username);
    }

    public function toAdmin(User $user): Admin
    {
        return new Admin($user->email, $user->user_id, $user->username);
    }
}
