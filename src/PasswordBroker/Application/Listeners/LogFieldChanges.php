<?php

namespace PasswordBroker\Application\Listeners;

use PasswordBroker\Application\Events\FieldEvent;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FieldEditLog\EventType;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\IsDeleted;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\Login;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\ValueEncrypted;
use PasswordBroker\Domain\Entry\Models\Fields\EntryFieldHistory;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Domain\Entry\Models\Fields\File;
use PasswordBroker\Domain\Entry\Models\Fields\Password;

#[\Attribute(\Attribute::TARGET_CLASS)]
class LogFieldChanges
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
     * @param FieldEvent $event
     * @return void
     */
    public function handle(FieldEvent $event): void
    {
        $fieldEditLog = new EntryFieldHistory();
        $fieldEditLog->field_id = $event->field->field_id;
        $fieldEditLog->title = $event->field->title;
        $fieldEditLog->type = Field::getRelated()[$event->field->getType()];
        $fieldEditLog->event_type = EventType::fromNative($event->getEventType());
        $fieldEditLog->login = $event->field->getType() === Password::TYPE
                ? $event->field->login
                : Login::fromNative(null);
        $fieldEditLog->value_encrypted = $event->field->getType() !== File::TYPE
            ? $event->field->value_encrypted
            : new ValueEncrypted('');
        $fieldEditLog->initialization_vector = $event->field->initialization_vector;
        $fieldEditLog->is_deleted = new IsDeleted($event->field->trashed());
        $fieldEditLog->updated_by = $event->field->updated_by;
        $fieldEditLog->save();
    }
}
