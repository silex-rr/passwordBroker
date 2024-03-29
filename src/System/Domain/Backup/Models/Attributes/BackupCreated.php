<?php

namespace System\Domain\Backup\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use Carbon\Carbon;
use OpenApi\Attributes\Schema;

#[Schema(schema: "System_BackupCreated", type: "string", format: "date-time")]
class BackupCreated extends AbstractValue
{
    public function __construct(mixed $value)
    {
        if (is_null($value)) {
            $this->value = $value;
            return;
        }
        if ($value instanceof Carbon) {
            $this->value = $value;
            return;
        }
        $this->value = new Carbon($value);
    }
}
