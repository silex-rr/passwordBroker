<?php

namespace PasswordBroker\Infrastructure\Validation\Contracts;

abstract class AbstractValidator
    implements ValidationHandler
{
    private ValidationHandler $validationHandler;

    public function __construct(ValidationHandler $validationHandler)
    {
        $this->validationHandler = $validationHandler;
    }

    public function handleError($error): void
    {
        $this->validationHandler->handleError($error);
    }

    abstract public function validate(): void;

    abstract public function getModel(): string;
}
