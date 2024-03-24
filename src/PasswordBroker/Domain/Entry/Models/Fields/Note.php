<?php

namespace PasswordBroker\Domain\Entry\Models\Fields;

use OpenApi\Attributes\Schema;

#[Schema(
    schema: "PasswordBroker_Note",
    allOf: [
        new Schema(ref: "#/components/schemas/PasswordBroker_Field"),
    ],
)]
class Note extends Field
{
    public const TYPE = 'note';

    protected $attributes = ['type' => self::TYPE];
}
