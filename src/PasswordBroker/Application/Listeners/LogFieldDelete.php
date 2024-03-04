<?php

namespace PasswordBroker\Application\Listeners;

use PasswordBroker\Application\Events\FieldEvent;

#[\Attribute(\Attribute::TARGET_CLASS)]
class LogFieldDelete
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
     * @param FieldEvent $event
     * @return void
     */
    public function handle(FieldEvent $event): void
    {
        $event->field->fieldHistories()->delete();
    }
}
