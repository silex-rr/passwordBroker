<?php

namespace PasswordBroker\Domain\Entry\Contracts;

interface RoleInterface
{
    public function getRoleName(): string;
}
