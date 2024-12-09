<?php

namespace PasswordBroker\Domain\Entry\Models\Fields;

use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\TOTPHashAlgorithm;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\TOTPTimeout;

/**
 * @property TOTPHashAlgorithm $totp_hash_algorithm
 * @property TOTPTimeout       $totp_timeout
 */
#[Schema(
    schema: "PasswordBroker_TOTP",
    allOf : [
        new Schema(ref: "#/components/schemas/PasswordBroker_Field"),
        new Property(property: "totp_hash_algorithm", ref: "#/components/schemas/PasswordBroker_TOTPHashAlgorithm"),
        new Property(property: "totp_timeout", ref: "#/components/schemas/PasswordBroker_TOPTTimeout"),
    ],
)]
class TOTP extends Field
{
    public const string TYPE            = 'TOTP';
    public const int    DEFAULT_TIMEOUT = 30;

    protected $attributes = ['type' => self::TYPE];

    public function __construct(array $attributes = [])
    {
        $this->hidden = array_filter($this->hidden, static fn($v) => !in_array($v, [
            'totp_hash_algorithm',
            'totp_timeout',
        ], true));

        parent::__construct($attributes);
    }
}
