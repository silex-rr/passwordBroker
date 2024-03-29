<?php

namespace System\Domain\Backup\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use InvalidArgumentException;
use OpenApi\Attributes\Schema;
use Ramsey\Uuid\Uuid;

#[Schema(schema: "System_BackupId", type: "string", format: "uuid")]
class BackupId extends AbstractValue
{
    public function __construct(?string $value)
    {
        if (is_null($value)) {
            $this->value = Uuid::uuid4()->toString();
            return;
        }

        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException('The given Backup ID is not valid Uuid');
        }

        $this->value = $value;
    }

    public function create(?string $uuid): static
    {
        return new static($uuid);
    }
}
