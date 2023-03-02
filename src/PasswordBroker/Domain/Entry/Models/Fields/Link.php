<?php

namespace PasswordBroker\Domain\Entry\Models\Fields;

class Link extends Field
{
    public const TYPE = 'link';

    protected $attributes = ['type' => self::TYPE];
}
