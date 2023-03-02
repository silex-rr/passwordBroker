<?php

namespace PasswordBroker\Domain\Entry\Services;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use PasswordBroker\Domain\Entry\Events\EntryWasCreated;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryValidationHandler;

class AddEntry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    //, SerializesModels;
    public function __construct(
        protected Entry                  $entry,
        protected EntryGroup             $entryGroup,
        protected EntryValidationHandler $entryValidationHandler
    )
    {
    }

//    public function getJobId()
//    {
//        return $this->job->getJobId();
//    }
//
//    public function getRawBody()
//    {
//        return $this->job->getRawBody();
//    }


    public function handle(): void
    {
        $this->entry->entry_id;
        $this->entry->entryGroup()->associate($this->entryGroup);

        $this->validate();
        $this->entry->save();
        event(new EntryWasCreated($this->entry));
    }

    public function validate(): void
    {
        $this->entry->validate($this->entryValidationHandler);
    }
}
