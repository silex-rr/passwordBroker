<?php

namespace PasswordBroker\Infrastructure\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

class EntryTitleDoesNotExistInEntryGroup implements Rule
{

    protected EntryGroup $entryGroup;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(EntryGroup $entryGroup)
    {
        $this->entryGroup = $entryGroup;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {

        return !$this->entryGroup->entries()->where('title', $value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Entry with the same Title already exists in this Entry Group.';
    }
}
