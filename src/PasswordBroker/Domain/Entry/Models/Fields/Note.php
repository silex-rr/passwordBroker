<?php

namespace PasswordBroker\Domain\Entry\Models\Fields;

class Note extends Field
{
    public const TYPE = 'note';

    protected $attributes = ['type' => self::TYPE];
}
