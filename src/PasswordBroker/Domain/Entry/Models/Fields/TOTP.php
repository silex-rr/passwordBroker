<?php

namespace PasswordBroker\Domain\Entry\Models\Fields;

use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\Login;

/**
 * @property Login $login
 */
#[Schema(
    schema: "PasswordBroker_TOTP",
    allOf: [
        new Schema(ref: "#/components/schemas/PasswordBroker_Field"),
    ],
)]
class TOTP extends Field
{
    public const string TYPE = 'TOTP';

    protected $attributes = ['type' => self::TYPE];
}
