<?php

namespace Identity\Application\Listeners;

use Identity\Domain\User\Events\UserRecoveryLinkWasCreated;
use Identity\Domain\User\Services\SendLetterWithRecoveryLink;
use Illuminate\Contracts\Bus\Dispatcher;

class SendRecoveryLink
{
    public function handle(UserRecoveryLinkWasCreated $linkWasCreated): void
    {
        /**
         * @var Dispatcher $dispatcher
         */
        $dispatcher = app(Dispatcher::class);
        $dispatcher->dispatch(new SendLetterWithRecoveryLink(
            recoveryLink: $linkWasCreated->recoveryLink
        ));
    }

}
