<?php

namespace Identity\Domain\User\Models\Attributes;

use App\Common\Domain\Contracts\EnumDefaultValue;
use App\Common\Domain\Traits\EnumToArray;
use OpenApi\Attributes\Schema;

#[Schema(
    schema: "System_RecoveryLinkType",
    type: "string",
    enum: [
        RecoveryLinkType::RECOVERY,
        RecoveryLinkType::INVITE,
    ]
)]
enum RecoveryLinkType: string implements EnumDefaultValue
{

    use EnumToArray;

    case RECOVERY = 'recovery';
    case INVITE = 'invite';

    public static function default(): self
    {
        return self::RECOVERY;
    }
}
