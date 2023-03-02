<?php

namespace PasswordBroker\Infrastructure\Validation;

use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Infrastructure\Validation\Contracts\AbstractValidator;
use PasswordBroker\Infrastructure\Validation\Contracts\ValidationHandler;

class EntryValidator extends AbstractValidator
{
    private Entry $entry;

    public function __construct(Entry $entry, ValidationHandler $validationHandler)
    {
        parent::__construct($validationHandler);
        $this->entry = $entry;
    }

    public function validate(): void
    {
        /**
         * @var EntryGroup $entryGroup;
         */
        $entryGroup = $this->entry->entryGroup()->first();
        if (is_null($entryGroup)) {
            $this->handleError('missingAnEntryGroup');
        }
//        dd([$this->entry->title->getValue(),
//            $entryGroup->entries()->first()
//            ]);
        if ($entryGroup->entries()->where('title', $this->entry->title)->exists()) {
            $this->handleError('titleAlreadyTaken');
        }


    }

    public function getModel(): string
    {
        return Entry::class;
    }
}
