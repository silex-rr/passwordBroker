<?php

namespace PasswordBroker\Application\Listeners;

use PasswordBroker\Application\Events\FieldEvent;
use PasswordBroker\Application\Events\FieldUpdated;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FieldEditLog\EventType;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\IsDeleted;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\Login;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\ValueEncrypted;
use PasswordBroker\Domain\Entry\Models\Fields\EntryFieldHistory;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Domain\Entry\Models\Fields\File;
use PasswordBroker\Domain\Entry\Models\Fields\Password;

class LogFieldDelete
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param FieldUpdated $event
     * @return void
     */
    public function handle(FieldEvent $event): void
    {
        $event->field->fieldHistories()->delete();
    }
}
