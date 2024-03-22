<?php

namespace System\Domain\Backup\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "System_BackupPassword", type: "string", nullable: true)]
class BackupPassword extends AbstractValue
{
    public function __construct(?string $value)
    {
        $this->value = $value;
    }

}
