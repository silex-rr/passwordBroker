<?php

namespace PasswordBroker\Domain\Entry\Services;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Events\EntryGroupWasCreated;
use PasswordBroker\Domain\Entry\Models\Attributes\GroupName;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryGroupValidationHandler;

class UpdateEntryGroup implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    public function __construct(
        protected EntryGroup $entryGroup,
        protected string $name,
        protected EntryGroupValidationHandler $entryGroupValidationHandler
    )
    {
    }

    public function handle(): void
    {
        $this->entryGroup->name = new GroupName($this->name);

        $this->validate();
        $this->entryGroup->save();

        event(new EntryGroupWasCreated($this->entryGroup));
    }

    public function validate(): void
    {
        $this->entryGroup->validate($this->entryGroupValidationHandler);
    }

}
