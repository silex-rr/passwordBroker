<?php

namespace PasswordBroker\Domain\Entry\Models\Fields;

use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\Login;

/**
 * @property Login $login
 */
#[Schema(
    schema: "PasswordBroker_Password",
    allOf: [
        new Schema(ref: "#/components/schemas/PasswordBroker_Field"),
        new Property(property: "updated_by", ref: "#/components/schemas/PasswordBroker_Login"),
    ],
)]
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
