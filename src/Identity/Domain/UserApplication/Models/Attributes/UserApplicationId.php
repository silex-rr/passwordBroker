<?php

namespace Identity\Domain\UserApplication\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class UserApplicationId extends AbstractValue
{
    /**
     * @param string|null $userApplicationId
     * @throws InvalidArgumentException
     */
    public function __construct(?string $userApplicationId = null)
    {
        if (is_null($userApplicationId)) {
            $userApplicationId = Uuid::uuid4()->toString();
        }

        if (!Uuid::isValid($userApplicationId)) {
            throw new InvalidArgumentException("Invalid user application ID '" . $userApplicationId . "'. Must match Uuid.");
        }

        $this->value = $userApplicationId;
    }

    public function create(): static
    {
        return new static();
    }
}
