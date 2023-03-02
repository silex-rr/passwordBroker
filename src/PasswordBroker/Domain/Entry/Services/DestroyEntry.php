<?php

namespace PasswordBroker\Domain\Entry\Services;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use PasswordBroker\Domain\Entry\Events\EntryWasDestroyed;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

class DestroyEntry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected Entry $entry,
        protected EntryGroup $entryGroup
    )
    {
    }


    public function handle(): void
    {
        $this->entry->delete();
        event(new EntryWasDestroyed(entry: $this->entry, entryGroup: $this->entryGroup));
    }
}
