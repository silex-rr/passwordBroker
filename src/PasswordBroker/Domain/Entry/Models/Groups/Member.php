<?php

namespace PasswordBroker\Domain\Entry\Models\Groups;

use PasswordBroker\Application\Events\RoleMemberCreated;
use PasswordBroker\Application\Events\RoleMemberDeleted;

class Member extends Role
{
    public const ROLE_NAME = 'member';

    protected $attributes = ['role' => self::ROLE_NAME];

    protected $dispatchesEvents = [
//        'saving' => FieldSave::class,
        'created' => RoleMemberCreated::class,
        'deleted' => RoleMemberDeleted::class,
//        'updated' => FieldUpdated::class,
//        'trashed' => FieldTrashed::class,
//        'restored' => FieldRestored::class,
//        'forceDeleted' => FieldForceDeleted::class,
    ];
}
