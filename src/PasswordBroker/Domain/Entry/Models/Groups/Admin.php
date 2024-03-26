<?php

namespace PasswordBroker\Domain\Entry\Models\Groups;

use OpenApi\Attributes\AdditionalProperties;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Events\RoleAdminCreated;
use PasswordBroker\Application\Events\RoleAdminDeleted;

#[Schema(
    schema: "PasswordBroker_Role_Admin",
    allOf: [
        new Schema(ref: "#/components/schemas/PasswordBroker_Role"),
    ],
    additionalProperties: new AdditionalProperties(properties: [
        new Property(property: "role", enum: ["admin"]),
    ])
)]
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
