<?php

namespace PasswordBroker\Infrastructure\Validation\Handlers;

use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Infrastructure\Validation\Contracts\ValidationHandler;

class EntryValidationHandler implements ValidationHandler
{

    public function handleError($error): void
    {
        $method = 'handle' . ucfirst($error) . 'Error';
        if (method_exists($this, $method)) {
            $this->{$method}();
        }
    }

    public function validate(): void
    {}

    public function getModel(): string
    {
        return Entry::class;
    }

    protected function handleMissingAnEntryGroupError(): void
    {
        throw new InvalidArgumentException('Entry must have an Entry Group');
    }
    protected function handleTitleAlreadyTakenError(): void
    {
        throw new InvalidArgumentException('Entry with this title already exists in this Entry Group');
    }
}
