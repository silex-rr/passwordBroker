<?php

namespace PasswordBroker\Domain\Entry\Services;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PasswordBroker\Domain\Entry\Events\FieldWasDestroyed;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Field;

class DestroyEntryField implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(
        protected Field $field,
        protected Entry $entry,
        protected EntryGroup $entryGroup,
    )
    {
    }


    public function handle(): void
    {
        $this->field->delete();
        event(new FieldWasDestroyed(field: $this->field, entry: $this->entry, entryGroup: $this->entryGroup));
    }
}
