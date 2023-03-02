<?php

namespace PasswordBroker\Domain\Entry\Services;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use PasswordBroker\Application\Services\EntryService;
use PasswordBroker\Domain\Entry\Events\EntryWasMoved;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

class MoveEntry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    private EntryService $entryService;
    public function __construct(
        protected Entry           $entry,
        protected EntryGroup      $entryGroupSource,
        protected EntryGroup      $entryGroupTarget,
        protected readonly string $master_password
    )
    {
        $this->entryService = app(EntryService::class);
    }


    public function handle(): void
    {
        $this->entryService->moveEntryToAnotherGroup(
            entry: $this->entry,
            entryGroupTarget: $this->entryGroupTarget,
            master_password: $this->master_password
        );
        event(new EntryWasMoved(
            entry: $this->entry,
            entryGroupSource: $this->entryGroupSource,
            entryGroupTarget: $this->entryGroupTarget
            )
        );
    }
}
