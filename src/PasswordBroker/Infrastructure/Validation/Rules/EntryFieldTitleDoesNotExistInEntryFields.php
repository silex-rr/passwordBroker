<?php

namespace PasswordBroker\Infrastructure\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\Fields\Field;

class EntryFieldTitleDoesNotExistInEntryFields implements Rule
{

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(protected Entry $entry, protected ?Field $field = null)
    {
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
        if ($this->field) {
            return !$this->entry->fields()
                ->where('field_id', "<>", $this->field->field_id->getValue())
                ->contains('title', $value);
        }

        return !$this->entry->fields()->contains('title', $value);
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
