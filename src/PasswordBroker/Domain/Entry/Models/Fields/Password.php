<?php

namespace PasswordBroker\Domain\Entry\Models\Fields;

class Password extends Field
{
    public const TYPE = 'password';

    protected $attributes = ['type' => self::TYPE];
}
