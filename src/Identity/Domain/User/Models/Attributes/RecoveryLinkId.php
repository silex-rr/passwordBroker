<?php

namespace Identity\Domain\User\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use InvalidArgumentException;
use OpenApi\Attributes\Schema;
use Ramsey\Uuid\Uuid;

#[Schema(schema: "Identity_RecoveryLinkId", type: "string", format: "uuid")]
class RecoveryLinkId extends AbstractValue
{

    /**
     * @param string|null $recoveryLinkId
     * @throws InvalidArgumentException
     */
    public function __construct(?string $recoveryLinkId = null)
    {
        if (is_null($recoveryLinkId)) {
//            throw new \RuntimeException(123);
            $recoveryLinkId = Uuid::uuid4()->toString();
        }

        if (!Uuid::isValid($recoveryLinkId)) {
            throw new InvalidArgumentException("Invalid Recovery Link ID. Must match Uuid.");
        }

        $this->value = $recoveryLinkId;
    }

    public function create(): static
    {
        return new static();
    }
}
