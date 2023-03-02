<?php

namespace PasswordBroker\Infrastructure\Validation;

use Identity\Domain\User\Models\User;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Infrastructure\Validation\Contracts\ValidationHandler;

class EntryGroupUserValidator extends Contracts\AbstractValidator
{
    public function __construct(
        private readonly EntryGroup $entryGroup,
        private readonly User       $user,
        ValidationHandler           $validationHandler)
    {

        parent::__construct($validationHandler);
    }


    public function validate(): void
    {
        // TODO: Implement validate() method.
    }

    public function getModel(): string
    {
        return EntryGroup::class;
    }
}
