<?php

namespace PasswordBroker\Domain\Entry\Services;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use PasswordBroker\Domain\Entry\Events\MemberWasRemovedFromEntryGroup;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Groups\Member;

class RemoveMemberFromEntryGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected Member $member,
        protected EntryGroup $entryGroup
    )
    {}

    public function handle(): void
    {

        $this->member->delete();
        event(new MemberWasRemovedFromEntryGroup($this->member));
    }
}
