<?php

namespace PasswordBroker\Domain\Entry\Models\Fields;

use OpenApi\Attributes\Schema;

#[Schema(
    schema: "PasswordBroker_Link",
    allOf: [
        new Schema(ref: "#/components/schemas/PasswordBroker_Field"),
    ],
)]
class Link extends Field
{
    public const TYPE = 'link';

    protected $attributes = ['type' => self::TYPE];
}
