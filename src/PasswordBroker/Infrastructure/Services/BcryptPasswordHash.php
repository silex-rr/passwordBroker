<?php

namespace PasswordBroker\Infrastructure\Services;

class BcryptPasswordHash
    extends AbstractPasswordHash
{
    public function hash(): string
    {
        return bcrypt($this->plain);
    }

}
