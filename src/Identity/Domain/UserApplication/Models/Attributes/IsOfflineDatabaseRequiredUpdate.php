<?php

namespace Identity\Domain\UserApplication\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use OpenApi\Attributes\Schema;

#[Schema(schema: "Identity_IsOfflineDatabaseRequiredUpdate", type: "boolean")]
class IsOfflineDatabaseRequiredUpdate extends AbstractValue
{
    public function __construct(bool $value)
    {
        $this->value = $value;
    }
}
