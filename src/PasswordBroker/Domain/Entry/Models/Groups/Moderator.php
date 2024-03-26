<?php

namespace PasswordBroker\Domain\Entry\Models\Groups;

use OpenApi\Attributes\AdditionalProperties;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Events\RoleModeratorCreated;
use PasswordBroker\Application\Events\RoleModeratorDeleted;

#[Schema(
    schema: "PasswordBroker_Role_Moderator",
    allOf: [
        new Schema(ref: "#/components/schemas/PasswordBroker_Role"),
    ],
    additionalProperties: new AdditionalProperties(properties: [
        new Property(property: "role", enum: ["moderator"]),
    ])
)]
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
