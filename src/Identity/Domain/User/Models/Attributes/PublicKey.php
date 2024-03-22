<?php

namespace Identity\Domain\User\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "Identity_PublicKey", type: "string", format: "binary")]
class PublicKey extends AbstractValue
{
    public function __construct(string $value) {
        $this->value = $value;
    }
}
