<?php

namespace PasswordBroker\Domain\Entry\Models\Groups;

class Member extends Role
{
    public const ROLE_NAME = 'member';

    protected $attributes = ['role' => self::ROLE_NAME];
}
