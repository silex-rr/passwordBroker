<?php

namespace PasswordBroker\Infrastructure\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;
use PasswordBroker\Domain\Entry\Models\Entry;

class EntryFieldTitleDoesNotExistInEntryFields implements Rule
{

    protected Entry $entry;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Entry $entry)
    {
        $this->entry = $entry;
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

        return !$this->entry->fields()->where('title', $value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Field with the same Title already exists in this Entry.';
    }
}
