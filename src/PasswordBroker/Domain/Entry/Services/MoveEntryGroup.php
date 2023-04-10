<?php

namespace PasswordBroker\Domain\Entry\Services;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Events\EntryGroupWasMoved;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

class MoveEntryGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected EntryGroup  $entryGroup,
        protected ?EntryGroup $entryGroupTarget,
        protected EntryGroupService $entryGroupService
    )
    {
    }

    public function handle(): void
    {
        if (is_null($this->entryGroupTarget)) {
            $this->entryGroup->parentEntryGroup()->dissociate();
        } else {
            $this->entryGroup->parentEntryGroup()->associate($this->entryGroupTarget);
        }
        $this->entryGroupService->rebuildMaterializedPath($this->entryGroup, $this->entryGroupTarget);

        event(
            new EntryGroupWasMoved(
                entryGroup: $this->entryGroup,
                entryGroupTarget: $this->entryGroupTarget
            )
        );
    }
}
