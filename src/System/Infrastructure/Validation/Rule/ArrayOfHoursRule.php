<?php

namespace System\Infrastructure\Validation\Rule;

use Illuminate\Contracts\Validation\Rule;

class ArrayOfHoursRule implements Rule
{
    private string $errorMessage = '';

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value): bool
    {
        if (!is_array($value)) {
            $this->errorMessage = 'Should be an array but ' . gettype($value) . ' was given';
            return false;
        }
        foreach ($value as $item) {
            if (!(is_int($item) || ctype_digit($item))
                || $item < 0 || $item > 23
            ) {
                $this->errorMessage = 'Wrong element in array should be a number between 0 and 23, but '
                    . ((string)$item) . ' was given';
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function message(): string
    {
        return $this->errorMessage;
    }
}
