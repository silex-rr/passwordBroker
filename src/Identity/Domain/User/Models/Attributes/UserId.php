<?php

namespace Identity\Domain\User\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class UserId extends AbstractValue
{

    /**
     * @param string|null $userId
     * @throws InvalidArgumentException
     */
    public function __construct(?string $userId = null)
    {
        if (is_null($userId)) {
            $userId = Uuid::uuid4()->toString();
        }

        if (!Uuid::isValid($userId)) {
            throw new InvalidArgumentException("Invalid user ID. Must match Uuid.");
        }

        $this->value = $userId;
    }

    public function create(): static
    {
        return new static();
    }
}
