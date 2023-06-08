<?php

namespace PasswordBroker\Domain\Entry\Models\Fields;

use PasswordBroker\Domain\Entry\Models\Fields\Attributes\Login;

/**
 * @property Login $login
 */
class Password extends Field
{
    public const TYPE = 'password';

    protected $attributes = ['type' => self::TYPE];

    public function __construct(array $attributes = array())
    {
        $this->hidden = array_filter($this->hidden, static fn($v) => $v !== 'login');

        parent::__construct($attributes);
    }
}
