<?php

namespace Identity\Domain\User\Listeners;

use Identity\Domain\User\Events\UserWasCreated;

class AddUserToLog
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param UserWasCreated $event
     * @return void
     */
    public function handle(UserWasCreated $event): void
    {
        //
    }
}
