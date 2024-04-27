<?php

namespace Identity\Domain\User\Models\Attributes;

use App\Common\Domain\Contracts\EnumDefaultValue;
use App\Common\Domain\Traits\EnumToArray;
use OpenApi\Attributes\Schema;

#[Schema(
    schema: "System_RecoveryLinkStatus",
    type: "string",
    enum: [
        RecoveryLinkStatus::AWAIT,
        RecoveryLinkStatus::IN_PROCESS,
        RecoveryLinkStatus::ACTIVATED,
        RecoveryLinkStatus::OUTDATED,
    ]
)]
enum RecoveryLinkStatus: string implements EnumDefaultValue
{
    use EnumToArray;

    case AWAIT = 'await';

    case IN_PROCESS = 'in_process';
    case ACTIVATED = 'activated';
    case OUTDATED = 'outdated';

    public static function default(): self
    {
        return self::AWAIT;
    }
}
