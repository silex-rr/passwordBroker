<?php

namespace PasswordBroker\Domain\Entry\Models\Fields\Attributes;

use App\Models\Abstracts\AbstractValue;
use InvalidArgumentException;
use OpenApi\Attributes\Schema;
use Ramsey\Uuid\Uuid;

#[Schema(schema: "PasswordBroker_FieldId", type: "string", format: "uuid",)]
class FieldId extends AbstractValue
{
    public function __construct(?string $value)
    {
        if (is_null($value)) {
            $this->value = Uuid::uuid4()->toString();
            return;
        }

        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException('The given Field ID is not valid Uuid');
        }

        $this->value = $value;
    }

    public function create(?string $uuid): static
    {
        return new static($uuid);
    }
}
