<?php

namespace PasswordBroker\Application\Broadcasting;

use Identity\Domain\User\Models\User;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Field;

class FieldChangesChannel
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param User $user
     * @param Field $field
     * @return bool
     */
    public function join(User $user, Field $field): bool
    {
        /**
         * @var Entry $entry
         */
        $entry = $field->entry()->firstOrFail();
        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = $entry->entryGroup()->firstOrFail();
        return $user->userOf()
                ->where('entry_group_id',
                    $entryGroup->entry_group_id->getValue()
                )->count() > 0;


    }
}
