<?php

namespace PasswordBroker\Domain\Entry\Services;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use PasswordBroker\Domain\Entry\Events\AdminWasRemovedFromEntryGroup;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Groups\Admin;

class RemoveAdminFromEntryGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected Admin $admin,
        protected EntryGroup $entryGroup
    )
    {}

    public function handle(): void
    {
        $this->admin->delete();
        event(new AdminWasRemovedFromEntryGroup($this->admin));
    }
}
