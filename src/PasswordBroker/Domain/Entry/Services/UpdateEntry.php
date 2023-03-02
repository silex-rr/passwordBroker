<?php

namespace PasswordBroker\Domain\Entry\Services;

use App\Models\Abstracts\AbstractValue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use PasswordBroker\Domain\Entry\Events\EntryWasUpdated;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryValidationHandler;

class UpdateEntry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    //, SerializesModels;
    public function __construct(
        protected Entry                  $entry,
        protected EntryGroup             $entryGroup,
        protected array                  $attributes,
        protected EntryValidationHandler $entryValidationHandler
    )
    {
    }

    public function handle(): void
    {
        foreach ($this->attributes as $key => $value) {
            if (! $value instanceof AbstractValue
                || $value->equals($this->entry->{$key})
                || in_array($key, $this->entry->getGuarded(), true)
            ) {
                continue;
            }
            $this->entry->{$key} = $value;
        }

        $this->validate();
        $this->entry->save();
        event(new EntryWasUpdated($this->entry));
    }

    public function validate(): void
    {
        $this->entry->validate($this->entryValidationHandler);
    }
}
