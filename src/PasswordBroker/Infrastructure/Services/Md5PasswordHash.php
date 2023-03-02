<?php

namespace PasswordBroker\Infrastructure\Services;

class Md5PasswordHash extends AbstractPasswordHash
{

    public function hash(): string
    {
        return md5($this->plain);
    }
}
