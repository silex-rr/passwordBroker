<?php

namespace System\Domain\Backup\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use Carbon\Carbon;
use OpenApi\Attributes\Schema;

#[Schema(schema: "System_BackupDeleted", type: "string", format: "date-time", nullable: true)]
class BackupDeleted extends AbstractValue
{
    public function __construct(?string $value)
    {
        if (is_null($value)) {
            $this->value = $value;
            return;
        }
        $this->value = new Carbon($value);
    }
}
