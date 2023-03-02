<?php

namespace PasswordBroker\Infrastructure\Validation\Handlers;

use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Infrastructure\Validation\Contracts\ValidationHandler;

class EntryGroupValidationHandler implements ValidationHandler
{

    public function handleError($error)
    {
        $method = 'handle' . ucfirst($error) . 'Error';
        if (method_exists($this, $method)) {
            return $this->{$method};
        }
        return null;
    }

    public function validate(): void
    {

    }

    public function getModel(): string
    {
        return EntryGroup::class;
    }
}
