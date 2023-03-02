<?php

namespace PasswordBroker\Infrastructure\Specifications;

use DateTimeImmutable;
use PasswordBroker\Domain\Entry\Contracts\EntrySpecificationInterface;
use PasswordBroker\Domain\Entry\Models\Fields\Password;

readonly class LatestEntrySpecifications
    implements EntrySpecificationInterface
{
    public function __construct(
        private DateTimeImmutable $since
    ){}

    public function specifies(Password $password): bool
    {
        return $password->created_at > $this->since;
    }
}
