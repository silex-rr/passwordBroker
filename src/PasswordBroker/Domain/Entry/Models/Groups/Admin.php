<?php

namespace PasswordBroker\Domain\Entry\Models\Groups;

class Admin extends Role
{
    public const ROLE_NAME = 'admin';

    protected $attributes = ['role' => self::ROLE_NAME];

}
