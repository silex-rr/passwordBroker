<?php

namespace PasswordBroker\Application\Listeners;

use Identity\Domain\User\Models\User;
use Illuminate\Support\Facades\Auth;
use PasswordBroker\Application\Events\FieldUpdated;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\IsDeleted;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\ValueEncrypted;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Domain\Entry\Models\Fields\FieldEditLog;
use PasswordBroker\Domain\Entry\Models\Fields\File;
use PasswordBroker\Domain\Entry\Models\Fields\Password;

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
     * @param FieldUpdated $event
     * @return void
     */
    public function handle(FieldUpdated $event): void
    {
        $fieldEditLog = new FieldEditLog();
        $fieldEditLog->field_id = $event->field->field_id;
        $fieldEditLog->title = $event->field->title;
        $fieldEditLog->type = Field::getRelated()[$event->field->getType()];
        $fieldEditLog->value_encrypted = $event->field->getType() !== File::TYPE ? $event->field->value_encrypted : new ValueEncrypted('');
        $fieldEditLog->is_deleted = new IsDeleted($event->field->trashed());
        $fieldEditLog->updated_by = $event->field->updated_by;
        $fieldEditLog->save();
    }
}
