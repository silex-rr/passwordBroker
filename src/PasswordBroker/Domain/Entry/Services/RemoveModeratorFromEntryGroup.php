<?php

namespace PasswordBroker\Domain\Entry\Services;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use PasswordBroker\Domain\Entry\Events\ModeratorWasRemovedFromEntryGroup;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Groups\Moderator;

class RemoveModeratorFromEntryGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue;

    public function __construct(
        protected Moderator $moderator,
        protected EntryGroup $entryGroup
    )
    {}

    public function handle(): void
    {
        $user_id = $this->moderator->user_id->getValue();
        $this->moderator->delete();
        event(new ModeratorWasRemovedFromEntryGroup(
            $user_id,
            $this->entryGroup
        ));
    }
}
