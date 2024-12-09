<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Attributes;

use App\Common\Domain\Contracts\EnumDefaultValue;
use App\Common\Domain\Traits\EnumToArray;
use OpenApi\Attributes\Schema;

#[Schema(schema: "PasswordBroker_TOTPHashAlgorithm", type: "string",)]
enum TOTPHashAlgorithm: string implements EnumDefaultValue
{
    use EnumToArray;

    case SHA1 = 'sha1';
    case SHA256 = 'sha256';
    case SHA512 = 'sha512';

    public static function default(): self
    {
        return self::SHA1;
    }
}
