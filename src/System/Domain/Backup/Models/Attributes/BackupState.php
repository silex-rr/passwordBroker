<?php

namespace System\Domain\Backup\Models\Attributes;

use App\Common\Domain\Contracts\EnumDefaultValue;
use OpenApi\Attributes\Schema;

#[Schema(
    schema: "System_BackupState",
    type: "string",
    enum: [
        BackupState::AWAIT,
        BackupState::CREATING,
        BackupState::CREATED,
        BackupState::ERROR,
        BackupState::DELETED,
    ]
)]
enum BackupState: string implements EnumDefaultValue
{
    case AWAIT = 'await';
    case CREATING = 'creating';
    case CREATED = 'created';
    case ERROR = 'error';
    case DELETED = 'deleted';

    public static function default(): self
    {
        return self::AWAIT;
    }
}
