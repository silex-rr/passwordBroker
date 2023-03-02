<?php

namespace PasswordBroker\Infrastructure\Validation;

use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Infrastructure\Validation\Contracts\ValidationHandler;

class EntryGroupValidator extends Contracts\AbstractValidator
{

    public function __construct(
        private readonly EntryGroup $entryGroup,
        ValidationHandler           $validationHandler
    )
    {
        parent::__construct($validationHandler);
    }
    public function validate(): void
    {
//        $this->handleError('some_errors');
    }

    public function getModel(): string
    {
        return EntryGroup::class;
    }
}
