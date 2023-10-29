<?php

namespace PasswordBroker\Domain\Entry\Models\Groups;

use PasswordBroker\Application\Events\RoleAdminCreated;
use PasswordBroker\Application\Events\RoleAdminDeleted;

class Admin extends Role
{
    public const ROLE_NAME = 'admin';

    protected $attributes = ['role' => self::ROLE_NAME];

    protected $dispatchesEvents = [
//        'saving' => FieldSave::class,
        'created' => RoleAdminCreated::class,
        'deleted' => RoleAdminDeleted::class,
//        'updated' => FieldUpdated::class,
//        'trashed' => FieldTrashed::class,
//        'restored' => FieldRestored::class,
//        'forceDeleted' => FieldForceDeleted::class,
    ];

}
