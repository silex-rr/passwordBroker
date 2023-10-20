<?php

namespace Identity\Domain\UserApplication\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class ClientId extends AbstractValue
{
    /**
     * @param string|null $clientId
     * @throws InvalidArgumentException
     */
    public function __construct(string $clientId)
    {
        if (!Uuid::isValid($clientId)) {
            throw new InvalidArgumentException("Invalid user client ID '" . $clientId . "'. Must match Uuid.");
        }

        $this->value = $clientId;
    }
}
