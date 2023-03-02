<?php

namespace PasswordBroker\Infrastructure\Validation\Contracts;

interface ValidationHandler
{
    public function handleError($error);
    public function validate(): void;
    public function getModel(): string;
}
