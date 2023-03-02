<?php

namespace PasswordBroker\Domain\Entry\Models\Groups;

class Moderator extends Role
{
    public const ROLE_NAME = 'moderator';

    protected $attributes = ['role' => self::ROLE_NAME];
}
