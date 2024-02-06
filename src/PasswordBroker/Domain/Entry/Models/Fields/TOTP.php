<?php

namespace PasswordBroker\Domain\Entry\Models\Fields;

use PasswordBroker\Domain\Entry\Models\Fields\Attributes\Login;

/**
 * @property Login $login
 */
class TOTP extends Field
{
    public const string TYPE = 'TOTP';

    protected $attributes = ['type' => self::TYPE];
}
