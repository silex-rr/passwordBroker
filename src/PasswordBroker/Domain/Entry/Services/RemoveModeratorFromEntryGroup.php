<?php

namespace PasswordBroker\Domain\Entry\Services;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use PasswordBroker\Domain\Entry\Events\ModeratorWasRemovedFromEntryGroup;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Groups\Moderator;

class RemoveModeratorFromEntryGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected Moderator $moderator,
        protected EntryGroup $entryGroup
    )
    {}

    public function handle(): void
    {
        $this->moderator->delete();
        event(new ModeratorWasRemovedFromEntryGroup($this->moderator));
    }
}
