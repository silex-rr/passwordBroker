<?php

namespace PasswordBroker\Domain\Entry\Models\Groups;

use PasswordBroker\Application\Events\RoleModeratorCreated;
use PasswordBroker\Application\Events\RoleModeratorDeleted;

class Moderator extends Role
{
    public const ROLE_NAME = 'moderator';

    protected $attributes = ['role' => self::ROLE_NAME];

    protected $dispatchesEvents = [
//        'saving' => FieldSave::class,
        'created' => RoleModeratorCreated::class,
        'deleted' => RoleModeratorDeleted::class,
//        'updated' => FieldUpdated::class,
//        'trashed' => FieldTrashed::class,
//        'restored' => FieldRestored::class,
//        'forceDeleted' => FieldForceDeleted::class,
    ];
}
