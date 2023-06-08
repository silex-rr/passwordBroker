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
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryGroupValidationHandler;

class AddEntryGroup implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    public function __construct(
        protected EntryGroup $entryGroup,
        protected EntryGroupValidationHandler $entryGroupValidationHandler
    )
    {
    }

//    public function getJobId(): string
//    {
//        return $this->job->getJobId();
//    }
//
//    public function getRawBody(): string
//    {
//        return $this->job->getRawBody();
//    }

    public function handle(): void
    {
        $this->validate();

        /**
         * @var EntryGroupService $entryGroupService
         */
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->rebuildMaterializedPath($this->entryGroup);
        $entryGroupService->addUserToGroupAsAdmin(Auth::user(), $this->entryGroup);
//        var_dump( $this->entryGroup->name->getValue());

        event(new EntryGroupWasCreated($this->entryGroup));
    }

    public function validate(): void
    {
        $this->entryGroup->validate($this->entryGroupValidationHandler);
    }

}
